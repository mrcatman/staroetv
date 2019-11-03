<?php

namespace App\Crossposting;


class VKCrossposter extends BaseCrossposter {

    public $name = "vk";
    public $public_name = "ВК";
    public $can_auto_connect = true;

    protected $params = [
        ["id" => "group_id", "name" => "ID группы"],
        ["id" => "app_id", "name" => "ID приложения"]
    ];

    public function getAutoConnectRedirectURI()
    {
        $oauth = new \VK\OAuth\VKOAuth();
        $client_id = 7193364;
        $redirect_uri = "https://oauth.vk.com/blank.html";
        $display = \VK\OAuth\VKOAuthDisplay::PAGE;
        $scope = [\VK\OAuth\Scopes\VKOAuthUserScope::WALL, \VK\OAuth\Scopes\VKOAuthUserScope::GROUPS, \VK\OAuth\Scopes\VKOAuthUserScope::OFFLINE];
        $state = 'secret_state_code';
        $revoke_auth = true;
        $browser_url = $oauth->getAuthorizeUrl(\VK\OAuth\VKOAuthResponseType::TOKEN, $client_id, $redirect_uri, $display, $scope, $state, null, $revoke_auth);
        return $browser_url;
    }

    public function createPost($data)
    {
        $token = $this->getSetting("access_token");
        if (!$token) {
            throw new \Exception("Не указан токен, возможно вы не авторизовались для постинга");
        }
        $message = "test";
        $vk = new \VK\Client\VKApiClient();
        $response = $vk->wall()->post($token, [
            'message' => $message
        ]);
        dd($response);
    }
}
