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
        $messages_in = PrivateMessage::where(['to_id' => $user->id])->get();
        $messages_out = PrivateMessage::where(['from_id' => $user->id])->get();
        $users_cache = [];
        $messages = $messages_in->merge($messages_out)->sortByDesc('created_at')->transform(function($message) use($users_cache, $user) {
            if ($message->to_id == $user->id) {
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
        return view("pages.pm.index", [
            'messages' => $messages
        ]);
    }

    public function show($id) {
        $user = auth()->user();
        if (!$user) {
            return redirect("/");
        }
        $message = PrivateMessage::find($id);
        if (!$message || ($message->from_id != $user->id && $message->to_id != $user->id)) {
            return redirect("/");
        }
        if ($message->to_id == $user->id) {
            $id = $message->from_id;
        } else {
            $id = $message->to_id;
        }
        $user = User::find($id);
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
        return view("pages.pm.form", [
            'user' => $to_user
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
            'to_id' => 'required'
        ]);
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
}
