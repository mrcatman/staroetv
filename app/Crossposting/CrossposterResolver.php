<?php

namespace App\Crossposting;

class CrossposterResolver {

    public $list = [
        'vk' => VKCrossposter::class
    ];

    public function get($name) {
        if (isset($this->list[$name])) {
            return new $this->list[$name];
        }
        return null;
    }

}