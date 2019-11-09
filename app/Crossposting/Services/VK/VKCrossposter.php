<?php

namespace App\Crossposting\Services\VK;

use App\Crossposting\BaseCrossposter;

class VKCrossposter extends BaseCrossposter {

    public $id = "vk";
    public $public_name = "ВК";
    public $can_auto_connect = true;

    protected $base_url = "https://api.vk.com/method/";
    protected $version = "5.103";


    public function __construct() {
        parent::__construct();
        $this->settings_manager = new VKSettingsManager($this);
    }

    public function getPostInstance() {
        return new VKPost();
    }

    public function isActive() {
        $token = $this->settings_manager->get("access_token");
        return (bool)$token;
    }
    
    public function getAutoConnectRedirectURI() {
        $client_id = $this->settings_manager->get('app_id');
        if (!$client_id) {
            throw new \Exception("Не указан id приложения");
        }
        $redirect_uri = urlencode("https://oauth.vk.com/blank.html");
        $scope = 335872;
        $url = "https://oauth.vk.com/authorize?client_id=$client_id&redirect_uri=$redirect_uri&display=page&scope=$scope&response_type=token&v=".$this->version."&revoke=1";
        return $url;
    }

    public function request($url, $params, $group_params = true) {
        $token = $this->settings_manager->get("access_token");
        if (!$token) {
            throw new \Exception("Не указан токен, возможно вы не авторизовались. Нажмите кнопку 'Подключить'");
        }
        $params['access_token'] = $token;
        if ($group_params) {
            $group_id = $this->settings_manager->get('group_id');
            if (!$group_id) {
                throw new \Exception("Не указан id группы");
            }
            $params['from_group'] = 1;
            $params['owner_id'] = "-".$group_id;

        }
        $params['v'] = $this->version;
        $request_url = $this->base_url.$url;
        $res = $this->client->request('POST', $request_url, [
            'form_params' => $params,
        ]);
        return json_decode($res->getBody()->getContents());
    }

    public function createPost($post) {
        if (!$post instanceof VKPost) {
            throw new \Exception("Неверный объект поста");
        }
        $text = $post->getText();
        $link = $post->getLink();
        $attachments_string = "";
        if ($link != "") {
            $attachments_string.= $link;
        }
        $params = [
            'message' => $text,
            'attachments' => $attachments_string
        ];
        $response = $this->request("wall.post", $params);
        if (isset($response->error)) {
            throw new \Exception("Ошибка: ".$response->error->error_msg);
        }
        return $response->response->post_id;
    }

    public function editPost($id, $post) {
        if (!$post instanceof VKPost) {
            throw new \Exception("Неверный объект поста");
        }
        $text = $post->getText();
        $link = $post->getLink();
        $attachments_string = "";
        if ($link != "") {
            $attachments_string.= $link;
        }
        $params = [
            'post_id' => $id,
            'message' => $text,
            'attachments' => $attachments_string
        ];
        $response = $this->request("wall.edit", $params);
        if (isset($response->error)) {
            throw new \Exception("Ошибка: ".$response->error->error_msg);
        }
        return $response->response->post_id;
    }

    public function deletePost($id) {
        $params = [
            'post_id' => $id,
        ];
        $response = $this->request("wall.delete", $params);
        return $response->response->post_id;
    }

    public function makeLinkById($post_id) {
        $group_id = $this->settings_manager->get('group_id');
        if (!$group_id) {
            throw new \Exception("Не указан id группы");
        }
        return "https://vk.com/wall-".$group_id."_".$post_id;
    }

}
