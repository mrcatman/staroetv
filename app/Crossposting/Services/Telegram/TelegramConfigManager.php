<?php

namespace App\Crossposting\Services\Telegram;

use App\Crossposting\ConfigManager;

class TelegramConfigManager extends ConfigManager {

    protected $settings = [
        ["id" => "access_token", "name" => "Access token бота"],
        ["id" => "group_id", "name" => "ID группы"],
        ["id" => "channel_name", "name" => "Короткий URL канала"],
    ];

}
