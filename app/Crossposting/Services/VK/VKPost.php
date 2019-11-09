<?php

namespace App\Crossposting\Services\VK;

use App\Crossposting\BasePost;

class VKPost extends BasePost {

    public function setText($text) {
        $this->text = strip_tags($text);
        return $this;
    }

}