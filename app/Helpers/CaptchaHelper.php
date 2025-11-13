<?php
namespace App\Helpers;
use Illuminate\Support\Facades\Cache;

class CaptchaHelper {

    public static function verify() {
        $recaptcha_response = request()->input('g-recaptcha-response');
        if (!Cache::has("recaptcha_" . $recaptcha_response)) {
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => 'https://www.google.com/recaptcha/api/siteverify',
                CURLOPT_POST => 1,
                CURLOPT_POSTFIELDS => [
                    'secret' => config('tokens.recaptcha'),
                    'response' => $recaptcha_response
                ]
            ]);
            $captcha_status = json_decode(curl_exec($curl));
            curl_close($curl);
            if (!isset($captcha_status->score) || $captcha_status->score < 0.5 ) {
                return false;
            } else {
                Cache::put("recaptcha_" . $recaptcha_response, 1, 600);
            }
        }
        return true;
    }
}
