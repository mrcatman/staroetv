<?php

namespace App\Http\Controllers;


use App\Comment;
use App\Helpers\BBCodesHelper;
use App\Helpers\DatesHelper;
use App\Helpers\PermissionsHelper;
use App\PrivateMessage;
use App\Record;
use App\User;
use App\UserMeta;
use Carbon\Carbon;

class PrivateMessagesController extends Controller {

    public function index() {
        $user = auth()->user();
        if (!$user) {
            return redirect("/");
        }
        $messages_in = PrivateMessage::where(['to_id' => $user->id, 'is_deleted_receiver' => false])->get();
        $messages_out = PrivateMessage::where(['from_id' => $user->id, 'is_deleted_sender' => false])->get();
        $messages_group = PrivateMessage::where(['is_group' => true])->where('group_ids', 'like', "%".$user->group_id.",%")->where(function($q) use($user) {
            $q->whereNull('deleted_ids');
            $q->orWhere('deleted_ids', 'not like', "%".$user->id.",%");
        })->get();
        $users_cache = [];
        $messages = $messages_in->merge($messages_out)->merge($messages_group)->sortByDesc('created_at')->transform(function($message) use($users_cache, $user) {
            if ($message->to_id == $user->id || $message->is_group) {
                $id = $message->from_id;
            } else {
                $id = $message->to_id;
            }
            if (!isset($users_cache[$id])) {
                $user = User::find($id);
                if ($user) {
                    $users[$id] = $user;
                    $message->user = $user;
                }
            } else {
                $message->user = $users_cache[$id];
            }
            return $message;
        });
        $can_mass_send = PermissionsHelper::allows('masspm');
        return view("pages.pm.index", [
            'can_mass_send' => $can_mass_send,
            'messages' => $messages
        ]);
    }

    public function show($id) {
        $user = auth()->user();
        if (!$user) {
            return redirect("/");
        }
        $message = PrivateMessage::find($id);
        $is_group = $message->is_group && strpos($message->group_ids, $user->group_id.",") !== false;
        if (!$message || ($message->from_id != $user->id && $message->to_id != $user->id && !$is_group)) {
            return redirect("/");
        }
        if ($message->to_id == $user->id || $is_group) {
            $id = $message->from_id;
        } else {
            $id = $message->to_id;
        }
        $user = User::find($id);
        if ($is_group) {
            if (strpos($message->read_ids, $user->id.",") === false) {
                $message->read_ids .= $user->id.",";
                $message->save();
            }
        } else {
            if (!$message->is_read) {
                $message->is_read = true;
                $message->save();
            }
        }
        return view("pages.pm.show", [
            'message' => $message,
            'user' => $user
        ]);
    }

    public function send() {
        $user = auth()->user();
        if (!$user) {
            return redirect("/");
        }
        $to_user = null;
        if (request()->has('user_id')) {
            $to_user = User::find(request()->input('user_id'));
        }
        $can_mass_send = PermissionsHelper::allows('masspm');
        return view("pages.pm.form", [
            'can_mass_send'  => $can_mass_send,
            'user' => $to_user,
        ]);
    }

    public function post() {
        if (!$user = auth()->user()) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        };
        $data = request()->validate([
            'title' => 'sometimes',
            'text' => 'required|min:1',
        ]);
        if (request()->input('is_group')) {
            if (!PermissionsHelper::allows('masspm')) {
                return [
                    'status' => 0,
                    'text' => 'Ошибка доступа'
                ];
            }
            $data['is_group'] = true;
            $data['group_ids'] = request()->input('group_ids').',';
        } else {
            $to_user = User::find($data['to_id']);
            if (!$to_user) {
                return [
                    'status' => 0,
                    'text' => 'Пользователь не найден'
                ];
            };
            if ($to_user->id == $user->id) {
                return [
                    'status' => 0,
                    'text' => 'Невозможно отправить сообщение самому себе'
                ];
            }
            $data['to_id'] = $to_user->id;
        }
        $message = new PrivateMessage($data);
        $message->text = BBCodesHelper::BBToHTML($message->text);
        $message->from_id = $user->id;
        $message->save();
        return [
            'status' => 1,
            'text' => 'Сообщение отправлено',
            'redirect_to' => '/pm'
        ];
    }

    public function delete() {
        if (!$user = auth()->user()) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }
        $message = PrivateMessage::find(request()->input('message_id'));
        if (!$message) {
            return [
                'status' => 0,
                'text' => 'Сообщение не найдено'
            ];
        }
        if ($message->from_id == $user->id) {
            $message->is_deleted_sender = true;
            $message->save();
        } elseif ($message->to_id == $user->id) {
            $message->is_deleted_receiver = true;
            $message->save();
        } elseif ($message->is_group && strpos($message->group_ids, $user->group_id.",") !== false) {
            if (strpos($message->deleted_ids, $user->id.",") === false) {
                $message->deleted_ids .= $user->id.",";
                $message->save();
            }
        } else {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }
        return [
            'status' => 1,
            'data' => [
                'dom' => [
                    [
                        'replace' => ".private-message[data-id=".$message->id."]",
                        'html' => ""
                    ]
                ]
            ]
        ];
    }

    public function cancel() {
        if (!$user = auth()->user() || !PermissionsHelper::allows('masspm')) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }
        $message = PrivateMessage::find(request()->input('message_id'));
        if (!$message) {
            return [
                'status' => 0,
                'text' => 'Сообщение не найдено'
            ];
        }
        $message->delete();
        return [
            'status' => 1,
            'data' => [
                'dom' => [
                    [
                        'replace' => ".private-message[data-id=".$message->id."]",
                        'html' => ""
                    ]
                ]
            ]
        ];
    }

    public function update() {
        return [
            'status' => 1,
            'data' => [
                'dom' => [
                    [
                        'replace' => ".auth-panel__pm__container",
                        'html' => view('blocks.pm')->render()
                    ]
                ]
            ]
        ];
    }
}
