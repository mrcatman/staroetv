<?php

namespace App\Http\Controllers\Auth;

use App\Constants\Actions;
use App\Helpers\ActionsLogHelper;
use App\Helpers\TelegramAuthHelper;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserMeta;
use Illuminate\Support\Facades\Auth;

class ProfileTelegramController extends Controller
{

    public function registerForm()
    {
        $data = TelegramAuthHelper::verify(request()->input('telegram_data'));
        $username = $data['username'] ?? null;
        if ($username && User::where(['username' => $data['username']])->count()) {
            $username = '';
        }
        return [
            'status' => 1,
            'data' => [
                'title' => 'Регистрация через Телеграм',
                'html' => view("blocks.auth.telegram-register-form", [
                    'username' => $username,
                    'telegram_data' => request()->input('telegram_data')
                ])->render()
            ]
        ];
    }

    public function register()
    {
        $telegram_data = TelegramAuthHelper::verify(json_decode(request()->input('telegram_data'), 1));
        $data = request()->validate([
            'username' => ['required', 'string', 'max:255', 'unique:users'],
        ]);

        if (User::where(['username' => $data['username']])->count()) {
            return [
                'status' => 0,
                'text' => 'Это имя пользователя уже занято'
            ];
        }
        if (User::where(['telegram_id' => $telegram_data['id']])->count()) {
            return [
                'status' => 0,
                'text' => 'Этот аккаунт уже привязан к другому профилю'
            ];
        }

        $user = new User();
        $user->telegram_id = $telegram_data['id'];
        $user->username = $data['username'];
        $user->group_id = 2;
        $user->ip_address_reg = request()->header('x-real-ip');
        $user->is_verified = true;
        if (!$user->name || $user->name == "") {
            $user->name = "-";
        }
        $user->save();
        $meta = new UserMeta(['user_id' => $user->id]);
        $meta->save();

        Auth::login($user);

        return [
            'status' => 1,
            'text' => 'Успешная регистрация',
            'redirect_to' => $user->url
        ];
    }

    public function connect()
    {
        $data = TelegramAuthHelper::verify(json_decode(request()->input('telegram_data'), 1));

        if (!$user = auth()->user()) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }

        if (User::where(['telegram_id' => $data['id']])->count()) {
            return [
                'status' => 0,
                'text' => 'Этот аккаунт уже привязан к другому профилю'
            ];
        }
        $user->telegram_id = $data['id'];

        ActionsLogHelper::create($user, Actions::Update);

        return [
            'status' => 1,
            'text' => 'Успешно привязано',
            'redirect_to' => route('profile.edit')
        ];
    }

    public function disconnect()
    {
        if (!$user = auth()->user()) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }

        $user->telegram_id = null;

        ActionsLogHelper::create($user, Actions::Update);

        return [
            'status' => 1,
            'text' => 'Успешно отвязано',
            'redirect_to' => route('profile.edit')
        ];

    }

}
