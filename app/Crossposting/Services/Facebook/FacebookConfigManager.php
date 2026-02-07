<?php

namespace App\Crossposting\Services\Facebook;

use App\Crossposting\ConfigManager;

class FacebookConfigManager extends ConfigManager {

    protected $settings = [
        ["id" => "ifttt_key", "name" => "Ключ IFTTT"],
        ["id" => "ifttt_event", "name" => "Название события IFTTT"],
    ];


}
