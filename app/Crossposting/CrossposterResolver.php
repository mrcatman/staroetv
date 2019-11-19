<?php

namespace App\Crossposting;

use App\Crossposting\Services\Telegram\TelegramCrossposter;
use App\Crossposting\Services\Twitter\TwitterCrossposter;
use App\Crossposting\Services\VK\VKCrossposter;

class CrossposterResolver {

    protected $list = [
        'vk' => VKCrossposter::class,
        'telegram' => TelegramCrossposter::class,
        'twitter' => TwitterCrossposter::class
    ];

    public function getList() {
        return array_values($this->list);
    }

    public function get($name) {
        if (isset($this->list[$name])) {
            return new $this->list[$name];
        }
        return null;
    }

}
