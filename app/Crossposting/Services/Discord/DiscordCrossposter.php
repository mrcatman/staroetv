<?php

namespace App\Crossposting\Services\Discord;

use App\Crossposting\BaseCrossposter;
use RestCord\DiscordClient;

class DiscordCrossposter extends BaseCrossposter {

    public $id = "discord";
    public $public_name = "Discord";
    public $can_auto_connect = false;
    protected $discord;

    public function __construct() {
        parent::__construct();
        $this->settings_manager = new DiscordSettingsManager($this);
        if ($this->settings_manager->get('bot_token')) {
            $this->discord = new DiscordClient(['token' => $this->settings_manager->get('bot_token')]);
        }
    }

    public function getPostInstance() {
        return new DiscordPost();
    }

    public function isActive() {
        $token = $this->settings_manager->get("bot_token");
        return (bool)$token;
    }


    public function createPost($post) {
        if (!$post instanceof DiscordPost) {
            throw new \Exception("Неверный объект поста");
        }

        $post_ids = [];

        $media = $post->getMedia();
        $params = $this->getRequestParams($post, count($media) > 0 ? $media[0] : null);
        $response = $this->discord->channel->createMessage($params);
        $post_ids[] = $response->toArray()['id'];

        if (count($media) > 1) {
            for ($i = 1; $i < count($media); $i++) {
                $params = $this->getRequestParams(null, $media[$i]);
                $response = $this->discord->channel->createMessage($params);
                $post_ids[] = $response->toArray()['id'];
            }
        }
        return implode(";", $post_ids);
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
        $params = [
            'content' => $text,
            'channel.id' => (int)$this->settings_manager->get('group_id'),
        ];
        if ($media) {
            if ($media['type'] == "video") {
                $params['content'] = $text == "..." ? $media['value'] : $text . PHP_EOL . PHP_EOL . $media['value'];
            } elseif ($media['type'] == "picture") {
                $params['embed'] = [
                   'image' => [
                       'url' =>  $this->getPictureFullUrl($media['value'])
                    ]
                ];
            }
        }
        return $params;
    }

    public function editPost($post_ids, $post) {
        $post_ids = explode(";", $post_ids);
        if (!$post instanceof DiscordPost) {
            throw new \Exception("Неверный объект поста");
        }
        $media = $post->getMedia();
        $need_update_media = $post->needUpdateField('media');
        if ($post->needUpdateField('text') || $post->needUpdateField('link') || count($need_update_media) > 0 && $need_update_media[0]) {
            $params = $this->getRequestParams($post, count($media) > 0 ? $media[0] : null);
            $params['message.id'] = (int)$post_ids[0];
            $this->discord->channel->editMessage($params);
        }
        if (count($need_update_media) > 1) {
            for ($i = 1; $i < count($need_update_media); $i++) {
                if ($need_update_media[$i]) {
                    if (isset($media[$i])) {
                        $params = $this->getRequestParams(null, $media[$i]);
                        if (isset($post_ids[$i])) {
                            $params['message.id'] = (int)$post_ids[$i];
                            $this->discord->channel->editMessage($params);
                        } else {
                            $response = $this->discord->channel->createMessage($params);
                            $post_ids[] = $response->toArray()['id'];
                        }
                    } else {
                        $post_to_delete_id = $post_ids[$i];
                        try {
                            $this->discord->channel->deleteMessage([
                                'channel.id' => (int)$this->settings_manager->get('group_id'),
                                'message.id' => (int)$post_ids[$i]
                            ]);
                        }  catch (\Exception $e) {

                        }
                        $post_ids = array_filter($post_ids, function($post_id) use ($post_to_delete_id) {
                            return $post_id != $post_to_delete_id;
                        });
                    }
                }
            }
        }
        return implode(";", $post_ids);
    }

    public function deletePost($post_ids) {
        $post_ids = explode(";", $post_ids);
        foreach ($post_ids as $post_id) {
            try {
                $this->discord->channel->deleteMessage([
                    'channel.id' => (int)$this->settings_manager->get('group_id'),
                    'message.id' => (int)$post_id
                ]);
            } catch (\Exception $e) {

            }
        }
        return $post_ids;
    }

}
