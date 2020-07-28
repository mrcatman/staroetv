<?php

namespace App\Notifications;

use App\ForumMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NewCommentReply extends Notification
{
    //use Queueable;

    protected $message;
    protected $quote;

    public function __construct($reply_to, $comment) {
        $this->reply_to = $reply_to;
        $this->comment = $comment;
    }

    public function via($notifiable)
    {
        return ['database'];
    }


    public function toArray() {
        return [
            'comment_id' => $this->comment->id,
            'comment_avatar' => $this->comment->user->avatar ? $this->comment->user->avatar->url : "",
            'comment_username' => $this->comment->user->username,
            'comment_text' => $this->comment->text,
            'comment_reply_to_id' => $this->reply_to->id,
            'comment_reply_to_text' => $this->reply_to->text
        ];
    }
}
