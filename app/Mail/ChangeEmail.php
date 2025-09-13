<?php

namespace App\Mail;

use App\Models\EmailChange;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ChangeEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, EmailChange $change)
    {
        $this->user = $user;
        $this->change = $change;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.change_mail', [
            'user' => $this->user,
            'url' => 'http://staroetv.su/users/change-email/'.$this->change->code
        ])->subject('Изменение email адреса');
    }
}
