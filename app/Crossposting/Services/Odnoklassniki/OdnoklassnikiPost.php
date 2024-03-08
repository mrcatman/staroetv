<?php

namespace App\Crossposting\Services\Odnoklassniki;

use App\Crossposting\BasePost;

class OdnoklassnikiPost extends BasePost {

    public function setText($text) {
        $this->text = strip_tags($text);
        return $this;
    }

}
