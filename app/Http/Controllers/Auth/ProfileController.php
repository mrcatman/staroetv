<?php

namespace App\Http\Controllers\Auth;

use App\Constants\Countries;
use App\Helpers\BBCodesHelper;
use App\Helpers\PermissionsHelper;
use App\Http\Controllers\Controller;
use App\Mail\ChangeEmail;
use App\Models\Comment;
use App\Models\EmailChange;
use App\Models\ForumMessage;
use App\Models\User;
use App\Models\UserMeta;
use App\Notifications\NewCommentReply;
use App\Notifications\NewForumReply;
use App\Notifications\NewMaterialComment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ProfileController extends Controller
{

    public function edit($id = null)
    {
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


    public function editPassword()
    {
        $user = auth()->user();

        return view("pages.users.password-form", [
            'user' => $user,
        ]);
    }


    public function save()
    {
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

        $rules = [
            'avatar_id' => 'sometimes',
            'username' => 'required|unique:users,username,' . $user->id,
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
        ];
        if ($user->email != '') {
            $rules['email'] = 'required|email|unique:users,email,' . $user->id;
        }

        $data = request()->validate($rules);
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
                    $value = Carbon::parse($value);
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
            'text' => $change_email ? 'Профиль обновлён. На новый e-mail адрес выслано письмо со ссылкой для подтверждения' : 'Профиль обновлён',
            'redirect_to' => !$change_email ? $user->url : ''
        ];
    }


    public function savePassword()
    {
        if (!auth()->user()) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }
        $user = auth()->user();

        if (!$user->password) {
            $data = request()->validate([
                'password' => 'required|confirmed|min:7',
            ]);
        } else {
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
        }
        $user->password = Hash::make($data['password']);
        $user->save();
        return [
            'status' => 1,
            'text' => 'Пароль изменен',
            'redirect_to' => $user->url
        ];
    }

    public function changeEmail($code)
    {
        $code = EmailChange::where(['code' => $code])->first();
        if ($code) {
            $user = User::find($code->user_id);
            $user->email = $code->email;
            $user->save();
            $code->delete();
            return redirect($user->url)->with('after_confirm', true);
        }
    }

    public function getNotifications()
    {
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

        $limit =  10;
        $page = (int)request()->input('page', 1);
        $total = $user->notifications()->count();

        $notifications = $user->notifications()->limit($limit)->offset(($page - 1) * $limit)->get();
        $notifications->transform(function ($notification) {
            $data = $notification->data;
            if ($notification->type == NewCommentReply::class) {
                $comment = null;
                if (isset($data['comment_id'])) {
                    $comment = Comment::find($data['comment_id']);
                }
                if (!$comment) {
                    $notification->delete();
                    return null;
                }
                $text = "<strong>" . $data['comment_username'] . "</strong> ответил вам в комментариях:";
                $short_content = Str::limit(strip_tags($data['comment_text']), 160, '...');
                $text .= "<div class='notification__quote'  data-full-text='" . $data['comment_text'] . "'>" . $short_content . "</div>";
                $link = $comment->url;
                $picture = $data['comment_avatar'];
                return (object)[
                    'picture' => $picture,
                    'text' => $text,
                    'link' => $link,
                    'time' => $notification->created_at->format('d.m.Y H:i')
                ];
            } elseif ($notification->type == NewForumReply::class) {
                $message = null;
                if (isset($data['message_id'])) {
                    $message = ForumMessage::find($data['message_id']);
                }
                if (!$message) {
                    $notification->delete();
                    return null;
                }
                $text = "<strong>" . $data['message_username'] . "</strong> ответил вам на форуме:";
                //$short_reply_to = Str::limit($data['message_reply_to'], 100, '...');
                $short_content = Str::limit(strip_tags($data['message_content']), 160, '...');
                $text .= "<div class='notification__quote'  data-full-text='" . $data['message_content'] . "'>" . $short_content . "</div>";
                $link = "/forum/0-" . $data['message_id'] . '#' . $data['message_id'];
                $picture = $data['message_avatar'];
                return (object)[
                    'picture' => $picture,
                    'text' => $text,
                    'link' => $link,
                    'time' => $notification->created_at->format('d.m.Y H:i')
                ];
            } elseif ($notification->type == NewMaterialComment::class) {
                $comment = null;
                if (isset($data['comment_id'])) {
                    $comment = Comment::find($data['comment_id']);
                }
                if (!$comment) {
                    $notification->delete();
                    return null;
                }

                $picture = $data['comment_avatar'];
                $text = "<strong>" . $data['comment_username'] . "</strong> оставил комментарий к вашему материалу:";
                $short_content = Str::limit(strip_tags($data['comment_text']), 160, '...');
                $text .= "<div class='notification__quote'  data-full-text='" . $data['comment_text'] . "'>" . $short_content . "</div>";
                $link = $comment->url;

                return (object)[
                    'picture' => $picture,
                    'text' => $text,
                    'link' => $link,
                    'time' => $notification->created_at->format('d.m.Y H:i')
                ];
            }
        })->filter(function ($notification) {
            return !!$notification;
        });

        $show_more = $total > $limit * $page;
        return [
            'status' => 1,
            'data' => [
                'show_more' => $show_more,
                'dom' => [
                    $page > 1 ? [
                        'append_to' => '.notifications__items',
                        'html' => view("blocks/notifications", ['show_more' => $show_more, 'only_list' => true, 'notifications' => $notifications])->render()
                    ] : [
                        'replace' => '.notifications__list',
                        'html' => view("blocks/notifications", ['show_more' => $show_more, 'only_list' => false, 'notifications' => $notifications])->render()
                    ]
                ]
            ]
        ];
    }


}
