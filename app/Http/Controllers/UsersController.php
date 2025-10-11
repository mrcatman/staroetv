<?php

namespace App\Http\Controllers;


use App\Constants\Countries;
use App\Helpers\BBCodesHelper;
use App\Helpers\DatesHelper;
use App\Helpers\PermissionsHelper;
use App\Mail\ChangeEmail;
use App\Models\Comment;
use App\Models\EmailChange;
use App\Models\ForumMessage;
use App\Models\Record;
use App\Models\User;
use App\Models\UserMeta;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class UsersController extends Controller {

    public function show($conditions) {
        $user = User::where($conditions)->first();

        if (!$user) {
            return view("pages.errors.404");
        }
        $videos_query = Record::approved()->where(['author_id' => $user->id, 'is_radio' => false])->orderBy('id', 'desc');
        $videos_count = $videos_query->count();
        $videos = $videos_query->limit(24)->get();

        $radio_recordings_query = Record::approved()->where(['author_id' => $user->id, 'is_radio' => true])->orderBy('id', 'desc');
        $radio_recordings_count = $radio_recordings_query->count();
        $radio_recordings = $radio_recordings_query->limit(24)->get();

        $banned_till = null;
        $is_banned_forever = $user->group_id == 255;
        if ($user->warnings->count() > 0) {
            $last_ban = $user->warnings->first();
            if ($last_ban->weight == 1 && $last_ban->time_expires > time()) {
                $banned_till = DatesHelper::formatTS($last_ban->time_expires);
            }
        }
        return view("pages.users.show", [
            'user' => $user,

            'banned_till' => $banned_till,
            'is_banned_forever' => $is_banned_forever,

            'videos' => $videos,
            'videos_count' => $videos_count,

            'radio_recordings' => $radio_recordings,
            'radio_recordings_count' => $radio_recordings_count,
        ]);
    }

    public function list() {
        $on_page = request()->input('on_page', 10);
        $search = "";
        $group_id = 0;
        $sort_by = "username";
        $sort_dir = "ASC";
        $available_sortings = ["username", "group_id", "created_at", "was_online"];
        if (PermissionsHelper::allows('usearch')) {
            $is_moderator = PermissionsHelper::allows('usedita');
            if (request()->has('sort_by') && in_array(request()->input('sort_by'), $available_sortings)) {
                $sort_by = request()->input('sort_by');
            }
            if ($sort_by == "was_online") {
                $sort_dir = "DESC";
            }
            $users = User::orderBy($sort_by, $sort_dir);
            if (request()->has('search')) {
                $search = request()->input('search');
                $field = "username";
                if (request()->has('search_field') && $is_moderator) {
                    $field = request()->input('search_field');
                }
                $users = $users->where($field, 'LIKE', '%'.$search.'%');
            }
            if (request()->has('group_id')) {
                $group_id = request()->input('group_id');
                if ($group_id > 0) {
                    $users = $users->where(['group_id' => $group_id]);
                }
            }

            $total = $users->count();
            $users = $users->paginate($on_page);
            $users = $users->appends(request()->except('page'));
            return view("pages.users.list", [
                'is_moderator' => $is_moderator,
                'sort_by' => $sort_by,
                'on_page' => $on_page,
                'group_id' => $group_id,
                'search' => $search,
                'total' => $total,
                'users' => $users
            ]);
        } else {
            return redirect("/");
        }
    }

    public function edit($id = null) {
        $user = auth()->user();
        $edit_id = null;
        if ($id && PermissionsHelper::allows('usedita')) {
            $edit_id = $id;
            $user = User::find($id);
        }
        if (!$user) {
            return redirect("/");
        }
        return view("pages.users.form", [
            'edit_id' => $edit_id,
            'user' => $user,
            'countries' => Countries::LIST
        ]);
    }

    public function editPassword() {
        $user = auth()->user();

        return view("pages.users.password-form", [
            'user' => $user,
        ]);
    }


    public function save() {
        if (!auth()->user()) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }
        $user = auth()->user();
        if (request()->has('user_id')) {
            if (PermissionsHelper::allows('usedita')) {
                $user = User::find(request()->input('user_id'));
                if (!$user) {
                    return [
                        'status' => 0,
                        'text' => 'Пользователь не найден'
                    ];
                }
            } else {
                return [
                    'status' => 0,
                    'text' => 'Ошибка доступа'
                ];
            }
        }
        $data = request()->validate([
            'avatar_id' => 'sometimes',
            'username' => 'required|unique:users,username,'.$user->id,
            'email' => 'required|email',
            'name' => 'sometimes',
            'date_of_birth' => 'sometimes',
            'country' => 'sometimes',
            'city' => 'sometimes',
            'avatar' => 'sometimes',
            'signature' => 'sometimes',
            'vk' => 'sometimes',
            'youtube' => 'sometimes',
            'yandex_video' => 'sometimes',
            'facebook' => 'sometimes',
            'user_comment' => 'sometimes'
        ]);
        $meta = $user->meta;
        if (!$meta) {
            $meta = new UserMeta(['user_id' => $user->id]);
        }
        $change_email = false;
        $meta_fields = ['date_of_birth', 'country', 'city', 'vk', 'youtube', 'yandex_video', 'facebook'];
        foreach ($data as $field => $value) {
            $value = trim($value);
            if (in_array($field, $meta_fields)) {
                if ($field === "date_of_birth") {
                    $value = Carbon::parse( $value);
                }
                $meta->{$field} = $value;
            } else {
                if ($field === "signature") {
                    $value = BBCodesHelper::BBToHTML($value);
                }
                if ($field === "email" && $user->email != $value && !request()->has('user_id')) {
                    $user_with_same_email = User::where(['email' => $value])->first();
                    if ($user_with_same_email) {
                        $error = \Illuminate\Validation\ValidationException::withMessages([
                            'email' => ['Другой пользователь с такой почтой уже зарегистрирован'],
                        ]);
                        throw $error;
                    }
                    $change = new EmailChange([
                        'user_id' => $user->id,
                        'email' => $value,
                        'code' => bin2hex(random_bytes(8))
                    ]);
                    $change->save();
                    Mail::to($value)->send(new ChangeEmail($user, $change));
                    $change_email = true;
                } else {
                    $user->{$field} = $value;
                }
            }
        }
        $user->save();
        $meta->save();
        return [
            'status' => 1,
            'text' => $change_email ? 'Сохранено. На новый e-mail адрес выслано письмо со ссылкой для подтверждения' : 'Сохранено'
        ];
    }

    public function savePassword() {
        if (!auth()->user()) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }
        $user = auth()->user();

        $data = request()->validate([
            'old_password' => 'required',
            'password' => 'required|confirmed|min:7',
        ]);
        if (!Hash::check($data['old_password'], $user->password)) {
            return [
                'status' => 0,
                'text' => 'Неверный старый пароль'
            ];
        }
        $user->password = Hash::make($data['password']);
        $user->save();
        return [
            'status' => 1,
            'text' => 'Пароль изменен'
        ];
    }

    public function changeEmail($code) {
        $code = EmailChange::where(['code' => $code])->first();
        if ($code) {
            $user = User::find($code->user_id);
            $user->email = $code->email;
            $user->save();
            $code->delete();
            return redirect($user->url)->with('after_confirm', true);
        }
    }

    public function autocomplete() {
        $count = 30;
        $users = User::select('id', 'username')->orderBy('was_online', 'desc');
        if (request()->has('term')) {
            $users = $users->where('username', 'LIKE', '%'.request()->input('term').'%');
        }
        $total = $users->count();
        $page = request()->input('page', 1);
        $users = $users->limit($count)->offset($count * ($page - 1))->get();
        return [
            'status' => 1,
            'data' => [
                'total' => $total,
                'users' => $users
            ]
        ];
    }

    public function getNotifications() {
        $user = auth()->user();
        if (!$user) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }
        foreach ($user->unreadNotifications as $notification) {
            $notification->markAsRead();
        }
        $list = $user->notifications;
        $notifications = [];
        foreach ($list as $notification) {
            $data = $notification->data;
            if ($notification->type == "App\Notifications\NewCommentReply") {
                $comment = null;
                if (isset($data['comment_id'])) {
                    $comment = Comment::find($data['comment_id']);
                }
                if (!$comment) {
                    $notification->delete();
                    continue;
                }
                $text = "<strong>" . $data['comment_username'] . "</strong> ответил вам в комментариях:";
                $short_content = Str::limit($data['comment_text'], 160, '...');
                $text .= "<div class='notification__quote'  data-full-text='" . $data['comment_text'] . "'>" . $short_content . "</div>";
                $link = $comment->url;
                $picture = $data['comment_avatar'];
                $notifications[] = (object)[
                    'picture' => $picture,
                    'text' => $text,
                    'link' => $link,
                    'time' => $notification->created_at->format('d.m.Y H:i')
                ];
            } elseif ($notification->type == "App\Notifications\NewForumReply") {
                $message = null;
                if (isset($data['message_id'])) {
                    $message = ForumMessage::find($data['message_id']);
                }
                if (!$message) {
                    $notification->delete();
                    continue;
                }
                $text = "<strong>" . $data['message_username'] . "</strong> ответил вам на форуме:";
                //$short_reply_to = Str::limit($data['message_reply_to'], 100, '...');
                $short_content = Str::limit($data['message_content'], 160, '...');
                $text .= "<div class='notification__quote'  data-full-text='" . $data['message_content'] . "'>" . $short_content . "</div>";
                $link = "/forum/0-" . $data['message_id'] . '#' . $data['message_id'];
                $picture = $data['message_avatar'];
                $notifications[] = (object)[
                    'picture' => $picture,
                    'text' => $text,
                    'link' => $link,
                    'time' => $notification->created_at->format('d.m.Y H:i')
                ];
            }
        }
        return [
            'status' => 1,
            'data' => [
                'dom' => [
                    [
                        'replace' => '.notifications__list',
                        'html' => view("blocks/notifications", ['notifications' => $notifications])->render()
                    ]
                ]
            ]
        ];
    }

    public function videos($id) {
        $user = User::find($id);
        if (!$user) {
            return redirect("/");
        }
        $records = Record::where(['is_radio' => false, 'author_id' => $id])->orderBy('id', 'desc')->paginate(30);
        return view("pages.users.records", [
            'page_title' => 'Видеозаписи',
            'records' => $records,
            'user' => $user,
        ]);
    }

    public function radioRecordings($id) {
        $user = User::find($id);
        if (!$user) {
            return redirect("/");
        }
        $records = Record::where(['is_radio' => true, 'author_id' => $id])->orderBy('id', 'desc')->paginate(30);
        return view("pages.users.records", [
            'page_title' => 'Радиозаписи',
            'records' => $records,
            'user' => $user,
        ]);
    }


}
