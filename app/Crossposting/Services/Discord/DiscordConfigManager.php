<?php

namespace App\Crossposting\Services\Discord;

use App\Crossposting\ConfigManager;

class DiscordConfigManager extends ConfigManager {

    protected $settings = [
        ["id" => "group_id", "name" => "ID группы"],
        ["id" => "bot_token", "name" => "Токен бота"],
    ];

}
