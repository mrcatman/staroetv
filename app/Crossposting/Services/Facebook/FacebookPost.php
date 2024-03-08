<?php

namespace App\Crossposting\Services\Facebook;

use App\Crossposting\BasePost;

class FacebookPost extends BasePost {

    public function setText($text) {
        $this->text = strip_tags($text);
        return $this;
    }

}
