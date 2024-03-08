<?php

namespace App\Crossposting\Services\Twitter;

use App\Crossposting\BasePost;

class TwitterPost extends BasePost {

    public function setText($text) {
        if (is_array($text)) {
            $this->multiple_texts = true;
            $this->text = $text;
            return $this;
        }
        $this->text = strip_tags($text);
        return $this;
    }

}
