<?php

namespace App\Notifications;

use App\Models\Comment;
use Illuminate\Notifications\Notification;

class NewMaterialComment extends Notification
{
    //use Queueable;

    protected Comment $comment;

    public function __construct(Comment $comment) {
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
        ];
    }
}
