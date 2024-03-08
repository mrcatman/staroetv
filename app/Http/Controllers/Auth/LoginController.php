<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\PermissionsHelper;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm() {
        return view('pages.auth.login');
    }

    public function login() {
        $login = request()->input('login');

        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $user = User::where([$field => $login])->first();
        if (!$user) {
            return [
                'status' => 0,
                'text' => 'Пользователь не найден'
            ];
        } elseif ($user->is_verified) {
            //return [
            //    'status' => 0,
            //    'text' => 'Вы не подтвердили почту'
            //];
        }
        $is_admin = PermissionsHelper::allows('admbar', $user);
        if (!$is_admin) {
            //return [
             //   'status' => 0,
             //   'text' => 'Вы не являетесь администратором сайта'
           // ];
        }
        if (Auth::attempt([$field => $login, 'password' => request()->input('password')], request()->has('remember'))) {

            return [
                'status' => 1,
                'text' => 'Успешный вход',
                'redirect_to' => '/index/8-'.User::where([$field => $login])->first()->id
            ];
        }
        return [
            'status' => 0,
            'text' => 'Неверные данные'
        ];
    }

    public function logout() {
        Auth::logout();
        return redirect('https://staroetv.su/');
    }
}
