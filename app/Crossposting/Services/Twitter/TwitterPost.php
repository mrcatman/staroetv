<?php

namespace App\Crossposting\Services\Twitter;

use App\Crossposting\BasePost;

class TwitterPost extends BasePost {

    public function setText($text) {
        $this->text = strip_tags($text);
        return $this;
    }

}
