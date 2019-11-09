<?php

namespace App\Crossposting\Services\Telegram;

use App\Crossposting\BaseCrossposter;

class TelegramCrossposter extends BaseCrossposter {

    public $id = "telegram";
    public $public_name = "Telegram";
    public $can_auto_connect = false;

    protected $base_url = "https://api.telegram.org/bot";

    public function __construct() {
        parent::__construct();
        $this->settings_manager = new TelegramSettingsManager($this);
    }

    public function getPostInstance() {
        return new TelegramPost();
    }

    public function isActive() {
        $token = $this->settings_manager->get("access_token");
        return (bool)$token;
    }

    public function request($url, $params) {
        $token = $this->settings_manager->get("access_token");
        if (!$token) {
            throw new \Exception("Не указан токен");
        }
        $group_id = $this->settings_manager->get("group_id");
        if (!$group_id) {
            throw new \Exception("Не указан id группы");
        }
        $params['chat_id'] = $group_id;
        $params['parse_mode'] = "markdown";

        $request_url = $this->base_url.$token."/".$url;
        $res = $this->client->request('POST', $request_url, [
            'json' => $params,
        ]);
        return json_decode($res->getBody()->getContents());
    }

    public function createPost($post) {
        if (!$post instanceof TelegramPost) {
            throw new \Exception("Неверный объект поста");
        }
        $text = $post->getText();
        $link = $post->getLink();
        $picture = $post->getPicture();
        if ($link != "") {
            $text.= PHP_EOL.PHP_EOL.$link;
        }
        if ($picture != "") {
            $url = "sendPhoto";
            $params = [
                'photo' => $picture,
                'caption' => $text,
            ];
        } else {
            $url =  "sendMessage";
            $params = [
                'text' => $text,
            ];
        }
        $response = $this->request($url, $params);
        return $response->result->message_id;
    }

    public function editPost($id, $post) {
        if (!$post instanceof TelegramPost) {
            throw new \Exception("Неверный объект поста");
        }
        $text = $post->getText();
        $link = $post->getLink();
        $picture = $post->getPicture();
        if ($link != "") {
            $text.= PHP_EOL.PHP_EOL.$link;
        }
        if ($picture != "") {
            $this->request("editMessageCaption", [
                'message_id' => $id,
                'caption' => $text
            ]);
            $this->request("editMessageMedia", [
                'message_id' => $id,
                'media' => [
                    'type' => 'photo',
                    'media' => $picture
                ]
            ]);
        } else {
            $this->request("editMessageText", [
                'message_id' => $id,
                'text' => $text
            ]);
        }
        return $id;
    }

    public function deletePost($id) {
        $params = [
            'post_id' => $id,
        ];
        $response = $this->request("wall.delete", $params);
        return $response->response->post_id;
    }

}
