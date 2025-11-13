<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\CaptchaHelper;
use App\Mail\VerifyAccount;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Models\UserMeta;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;

class RegisterController extends Controller
{
    public function showRegistrationForm() {
        return view('pages.auth.register');
    }

    public function register()
    {
        if (!CaptchaHelper::verify()) {
            return [
                'status' => 0,
                'text' => 'Скорее всего вы робот :(',
            ];
        }

        if (!request()->input('rules')) {
            return [
                'status' => 0,
                'text' => 'Почитайте правила сайта, пожалуйста!',
            ];
        }
        $data = request()->validate([
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'rules' => ['accepted']
        ]);

        $user = new User();
        $user->username = $data['username'];
        $user->email = $data['email'];
        $user->password = Hash::make($data['password']);
        if (request()->has('name')) {
            $user->name = request()->input('name');
        }
        $user->group_id = 2;
        $user->ip_address_reg = request()->header('x-real-ip');
        $user->verify_code = bin2hex(random_bytes(8));
        $user->is_verified = false;
        if (!$user->name || $user->name == "") {
            $user->name = "-";
        }
        $user->save();
        $meta = new UserMeta(['user_id' => $user->id]);
        $meta->save();
        Mail::to($user)->send(new VerifyAccount($user));
        Auth::login($user);
        return [
            'status' => 1,
            'text' => 'Успешная регистрация',
            'redirect_to' => $user->url
        ];
    }

    public function confirm($code) {
        $user = User::where(['verify_code' => $code])->first();
        if ($user) {
            $user->verify_code = null;
            $user->is_verified = true;
            $user->save();
            Auth::login($user);
            return redirect($user->url)->with('after_confirm', true);
        }
    }

}
