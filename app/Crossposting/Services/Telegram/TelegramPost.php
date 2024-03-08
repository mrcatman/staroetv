<?php

namespace App\Crossposting\Services\Telegram;

use App\Crossposting\BasePost;

class TelegramPost extends BasePost {

    public function setText($text) {
        $this->text = strip_tags($text);
        return $this;
    }


    public function getLinkText() {
        if (is_array($this->link)) {
            return "<a href='".$this->link[0]."'>".$this->link[1]."</a>";
        } else {
            return $this->link;
        }
    }
}
