<?php

namespace App\Crossposting\Services\Twitter;

use App\Crossposting\ConfigManager;

class TwitterConfigManager extends ConfigManager {

    protected $settings = [
        ["id" => "oauth_consumer_key", "name" => "OAuth consumer key"],
        ["id" => "oauth_consumer_secret", "name" => "OAuth consumer secret"],
        ["id" => "oauth_token", "visible" => false],
        ["id" => "oauth_token_secret", "visible" => false],
        ["id" => "user_id", "visible" => false],
        ["id" => "screen_name", "visible" => false],
    ];

}
