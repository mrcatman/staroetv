<?php

namespace App\Crossposting\Services\Discord;

use App\Crossposting\BasePost;

class DiscordPost extends BasePost {

    public function setText($text) {
        $this->text = strip_tags($text);
        return $this;
    }

}
