<?php

namespace App\Http\Controllers\Auth;

use App\Mail\VerifyAccount;
use App\User;
use App\Http\Controllers\Controller;
use App\UserMeta;
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
        $recaptcha_response = request()->input('g-recaptcha-response');
        if (!Cache::has("recaptcha_" . $recaptcha_response)) {
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => 'https://www.google.com/recaptcha/api/siteverify',
                CURLOPT_POST => 1,
                CURLOPT_POSTFIELDS => [
                    'secret' => "6LccwdUZAAAAAM_qSrMovsqGl3WQKmGjag1n0OkW",
                    'response' => $recaptcha_response
                ]
            ]);
            $captcha_status = json_decode(curl_exec($curl));
            curl_close($curl);
            if ($captcha_status->score < 0.5) {
                return [
                    'status' => 0,
                    'text' => 'Скорее всего вы робот :(',
                ];
            } else {
                Cache::put("recaptcha_" . $recaptcha_response, 1, 600);
            }
        }
        $data = request()->validate([
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
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
