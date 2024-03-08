<?php

namespace App\Crossposting\Services\Facebook;

use App\Crossposting\BaseSettingsManager;

class FacebookSettingsManager extends BaseSettingsManager {

    protected $settings = [
        ["id" => "ifttt_key", "name" => "Ключ IFTTT"],
        ["id" => "ifttt_event", "name" => "Название события IFTTT"],
    ];


}
