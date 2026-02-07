<?php

namespace App\Crossposting\Services\Telegram;

use App\Crossposting\Post;

class TelegramPost extends Post {

    public function getParam(string $param): mixed {
        if ($param == 'link_text' && isset($this->params['link']) && is_array($this->params['link'])) {
            return "<a href='".$this->params['link'][0]."'>".$this->params['link'][1]."</a>";
        }
        return parent::getParam($param);
    }

}
