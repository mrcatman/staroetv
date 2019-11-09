<?php

namespace App\Crossposting\Services\Telegram;

use App\Crossposting\BasePost;

class TelegramPost extends BasePost {

    public function setText($text) {
        $this->text = strip_tags($text);
        return $this;
    }

}