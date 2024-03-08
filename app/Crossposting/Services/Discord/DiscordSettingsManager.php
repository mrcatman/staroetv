<?php

namespace App\Crossposting\Services\Discord;

use App\Crossposting\BaseSettingsManager;

class DiscordSettingsManager extends BaseSettingsManager {

    protected $settings = [
        ["id" => "group_id", "name" => "ID группы"],
        ["id" => "bot_token", "name" => "Токен бота"],
    ];

}
