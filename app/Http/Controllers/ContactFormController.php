<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionsHelper;
use App\Mail\ContactMessage;
use App\Mail\VerifyAccount;
use App\User;
use App\UserWarning;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class ContactFormController extends Controller {

    protected $admin_email = "kittenizator@yandex.ru";

    public function show() {
        return view("pages.contact.index");
    }

    public function send() {
        $recaptcha_response = request()->input('g-recaptcha-response');
        if (!Cache::has("recaptcha_".$recaptcha_response)) {
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
                    'text' => 'Вы, скорее всего, робот :( Но если всё-таки нет, напишите ваш вопрос напрямую на kittenizator@yandex.ru',
                ];
            } else {
                Cache::put("recaptcha_" . $recaptcha_response, 1, 600);
            }
        }
        $data = request()->validate([
            'name' => 'required',
            'contact' => 'required',
            'text' => 'required',
        ]);
        Mail::to($this->admin_email)->send(new ContactMessage($data));
        return [
            'status' => 1,
            'text' => 'Ваше сообщение отправлено. В ближайшее время с вами свяжется администратор сайта'
        ];
    }
}
