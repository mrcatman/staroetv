<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DigitizationMessage extends Mailable
{
    use Queueable, SerializesModels;

    public $data = [];

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.digitization', [
            'data' => $this->data
        ])->subject('Новая заявка на оцифровку');
    }
}
