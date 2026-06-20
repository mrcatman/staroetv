<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\CaptchaHelper;
use App\Helpers\GeolocationHelper;
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

    public function register(
        GeolocationHelper $geolocation
    )
    {
//        if (!CaptchaHelper::verify()) {
//            return [
//                'status' => 0,
//                'text' => 'Скорее всего вы робот :(',
//            ];
//        }

        if (!request()->input('rules')) {
            return [
                'status' => 0,
                'text' => 'Почитайте правила сайта, пожалуйста!',
            ];
        }

        $data = request()->validate([
            'username' => ['required', 'string', 'max:32', 'unique:users'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'rules' => ['accepted'],
            'name' => ['sometimes', 'string', 'max:64']
        ]);

        $user = new User();
        $user->username = $data['username'];
        $user->email = $data['email'];
        $user->password = Hash::make($data['password']);
        $user->group_id = 2;

        $user->ip_address_reg = request()->header('x-real-ip');
        $country = $geolocation->country($user);
        $forbidden_countries = explode(',',config('site.geoip_forbidden_countries'));

        if (in_array($country, $forbidden_countries)) {
            return [
                'status' => 0,
                'text' => 'Скорее всего вы спамер и вам тут будут не рады :(',
            ];
        }

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
