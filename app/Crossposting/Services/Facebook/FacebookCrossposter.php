<?php

namespace App\Crossposting\Services\Facebook;

use App\Crossposting\BaseCrossposter;

class FacebookCrossposter extends BaseCrossposter {

    public $id = "facebook";
    public $public_name = "Facebook";
    public $can_auto_connect = false;
    public $can_edit_posts = false;
    public $can_delete_posts = false;

    public function __construct() {
        parent::__construct();
        $this->settings_manager = new FacebookSettingsManager($this);
    }

    public function getPostInstance() {
        return new FacebookPost();
    }

    public function isActive() {
        $token = $this->settings_manager->get("ifttt_key");
        return (bool)$token;
    }




    public function request($params) {
        $key = $this->settings_manager->get("ifttt_key");
        if (!$key) {
            throw new \Exception("Не указан ключ");
        }
        $event = $this->settings_manager->get("ifttt_event");
        if (!$event) {
            throw new \Exception("Не указано название события");
        }
        $url = "https://maker.ifttt.com/trigger/$event/with/key/$key";
        $res = $this->client->request("POST", $url, [
            \GuzzleHttp\RequestOptions::JSON => $params
        ]);
        return $res;
    }


    public function getRequestParams($post, $media = null) {
        if ($post) {
            $text = $post->getText();
            $link = $post->getLinkText();
            if ($link != "") {
                $text.= PHP_EOL.PHP_EOL.$link;
            }
        } else {
            $text = isset($media['text']) && $media['text'] != "" ? $media['text'] : "...";
        }

        if ($media) {
            $text .= PHP_EOL.PHP_EOL.$media['value'];
        }
        $params = [
            'value2' => $text
        ];
        if ($post && $post->getLinkValue()) {
            $text.= PHP_EOL.$post->getLinkText();
            $params['value2'] = $text;
            $params['value1'] = $post->getLinkValue();
        } elseif ($media) {
            $params['value1'] = $media['value'];
        }
        return $params;
    }

    public function createPost($post) {
        if (!$post instanceof FacebookPost) {
            throw new \Exception("Неверный объект поста");
        }

        $media = $post->getMedia();
        $params = $this->getRequestParams($post, count($media) > 0 ? $media[0] : null);
        $this->request($params);

        if (count($media) > 1) {
            for ($i = 1; $i < count($media); $i++) {
                $params = $this->getRequestParams(null, $media[$i]);
                $this->request($params);
            }
        }
        return "";
    }

    public function editPost($id, $post) {
        throw new \Exception("Невозможно редактировать посты, внесите изменения вручную");
    }

    public function deletePost($id) {
        throw new \Exception("Невозможно удалять посты, сделайте это вручную");
    }

    public function makeLinkById($post_id) {
        $group_id = $this->settings_manager->get('group_id');
        if (!$group_id) {
            throw new \Exception("Не указан id группы");
        }
        return "https://vk.com/wall-".$group_id."_".$post_id;
    }

}
