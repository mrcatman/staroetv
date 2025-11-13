<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Mailable;

use App\Helpers\CaptchaHelper;
use App\Mail\ContactMessage;
use App\Mail\DigitizationMessage;

class ContactFormController extends Controller {

    private function sendToAdmin(Mailable $message)
    {
        Mail::to(config('site.admin_email'))->send($message);

        $text = $message->subject.PHP_EOL.$message->render();
        $text = str_replace('<br>', '', $text);
        $text = str_replace('<hr>', '________________', $text);
        Http::post('https://api.telegram.org/bot'.config('tokens.telegram').'/sendMessage', [
            'chat_id' => config('site.admin_telegram_id'),
            'text' => $text,
            'parse_mode' => 'html',
        ]);
    }

    public function show() {
        return view("pages.contact.index");
    }

    public function digitization()
    {
        return view("pages.contact.digitization");
    }

    public function send() {
        CaptchaHelper::verify();
        $data = request()->validate([
            'name' => 'required',
            'contact' => 'required',
            'text' => 'required',
        ]);
        $this->sendToAdmin(new ContactMessage($data));
        return [
            'status' => 1,
            'text' => 'Ваше сообщение отправлено. В ближайшее время с вами свяжется администратор сайта'
        ];
    }

    public function digitizationSend() {
        CaptchaHelper::verify();
        $data = request()->validate([
            'city' => 'required',
            'name' => 'required',
            'contact' => 'required',
            'text' => 'required',
        ]);
        $this->sendToAdmin(new DigitizationMessage($data));
        return [
            'status' => 1,
            'text' => 'Ваша заявка отправлена. В ближайшее время с вами свяжется администратор сайта'
        ];
    }
}
