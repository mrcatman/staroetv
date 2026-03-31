<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\TelegramAuthHelper;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm() {
        if (auth()->user()) {
            return redirect(route('index'));
        }
        return view('pages.auth.login');
    }

    public function login() {

        $login = request()->input('login');

        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
       // $user = User::where([$field => $login])->first();
//        if (!$user) {
//            return [
//                'status' => 0,
//                'text' => 'Пользователь не найден'
//            ];
//        } elseif ($user->is_verified) {
            //return [
            //    'status' => 0,
            //    'text' => 'Вы не подтвердили почту'
            //];
//        }

//        $is_admin = PermissionsHelper::allows('admbar', $user);
//        if (!$is_admin) {
//            //return [
//             //   'status' => 0,
//             //   'text' => 'Вы не являетесь администратором сайта'
//           // ];
//        }
        if (Auth::attempt([$field => $login, 'password' => request()->input('password')], request()->has('remember'))) {
            $user = User::where([$field => $login])->first();
            if (!$user->telegram_id && request()->input('telegram_data') != '') {
                $data = TelegramAuthHelper::verify(json_decode(request()->input('telegram_data'), 1));
                if (!User::where(['telegram_id' => $data['id']])->count()) {
                    $user->telegram_id = $data['id'];
                    $user->save();
                }
            }
            return [
                'status' => 1,
                'text' => 'Успешный вход',
                'redirect_to' => $user->url
            ];
        } elseif (request()->input('telegram_data') != '') {
            return $this->loginUsingTelegram();
        }
        return [
            'status' => 0,
            'text' => 'Неверные данные'
        ];
    }

    public function loginUsingTelegram()
    {
        $data = TelegramAuthHelper::verify(json_decode(request()->input('telegram_data'), 1));
        $user = User::where(['telegram_id' => $data['id']])->first();
        if ($user) {
            Auth::login($user);
            return [
                'status' => 1,
                'text' => 'Успешный вход',
                'redirect_to' => route('users.show', $user)
            ];
        } else {
            return [
                'status' => 1,
                'is_new_user' => true
            ];
        }
    }

    public function logout() {
        Auth::logout();
        return redirect()->back();
        //return redirect(route('index'));
    }
}
