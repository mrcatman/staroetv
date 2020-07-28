<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Http\Controllers\Controller;
use App\UserMeta;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;

class RegisterController extends Controller
{
    public function showRegistrationForm() {
        return view('pages.auth.register');
    }

    public function register() {
        $data = request()->validate([
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'captcha' => ['required', 'captcha']
        ]);
        $user = new User();
        $user->username = $data['username'];
        $user->email = $data['email'];
        $user->password = Hash::make($data['password']);
        if (request()->has('name')) {
            $user->name = request()->input('name');
        }
        $user->group_id = 1;
        $user->ip_address_reg = request()->ip();
        $user->was_online = Carbon::now();
        $user->save();
        Auth::login($user);
        $meta = new UserMeta(['user_id' => $user->id]);
        $meta->save();
        return [
            'status' => 1,
            'text' => 'Успешная регистрация',
            'redirect_to' => '/users/'.$user->id
        ];
    }

}
