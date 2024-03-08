<?php

namespace App\Crossposting\Services\Odnoklassniki;

use App\Crossposting\BaseSettingsManager;

class OdnoklassnikiSettingsManager extends BaseSettingsManager {

    protected $settings = [
        ["id" => "group_id", "name" => "ID группы"],
        ["id" => "access_token", "name" => "Токен"],
        ["id" => "session_secret_key", "name" => "Секретный ключ сессии"],
        ["id" => "public_key", "name" => "Публичный ключ приложения"],
        ["id" => "secret_key", "name" => "Секретный ключ приложения"],
        ["id" => "app_id", "name" => "ID приложения"],
        ["id" => "group_name", "name" => "Короткий URL группы"],
    ];


}
