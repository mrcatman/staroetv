<?php

namespace App\Notifications;

use App\ForumMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NewForumReply extends Notification
{
    //use Queueable;

    protected $message;
    protected $quote;

    public function __construct(ForumMessage $message, $quote) {
        $this->quote = $quote;
        $this->message = $message;
    }

    public function via($notifiable)
    {
        return ['database'];
    }


    public function toArray() {
        return [
            'message_id' => $this->message->id,
            'message_avatar' => $this->message->user->avatar ? $this->message->user->avatar->url : "",
            'message_username' => $this->message->username,
            'message_content' => $this->quote['text'],
            'message_reply_to' => $this->quote['reply_to']
        ];
    }
}
