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
        $params['parse_mode'] = "html";

        $request_url = $this->base_url.$token."/".$url;
        $res = $this->client->request('POST', $request_url, [
            'json' => $params,
        ]);
        return json_decode($res->getBody()->getContents());
    }

    protected function getRequestParamsForMedia($media_item, $post = null) {
        $method = null;
        $params = null;
        if ($media_item['type'] == "picture") {
            if ($media_item['value'][0] == "/") {
                $method = "sendPhoto";
                $text = $this->getPostText($post, $media_item);
                $params = [
                    'photo' => $this->getPictureFullUrl($media_item['value']),
                    'caption' => $text,
                ];
            }
        } elseif ($media_item['type'] == "video") {
            $text = $this->getPostText($post, $media_item);


            $method = "sendMessage";
            $params = [
                'text' => $text
            ];
        }
        return [
            'method' => $method,
            'params' => $params
        ];
    }


    protected function getPostText($post = null, $media_item = null) {
        if (!$media_item && !$post) {
            return "";
        }
        if ($media_item && isset($media_item['text']) && $media_item['text'] != "") {
            $text = $media_item['text'];
            if ($media_item['type'] == "video") {
                $text.= PHP_EOL.$media_item['value'];
            }
        } elseif ($post) {
            $text = $post->getText();
            if ($media_item && $media_item['type'] == "video") {
                $text .= PHP_EOL;
                $text .= $media_item['value'];
            }
            $link = $post->getLinkText();
            if ($link != "") {
                $text.= PHP_EOL.PHP_EOL.$link;
            }
        } elseif ($media_item['type'] == "video") {
            $text = $media_item['value'];
        } else {
            $text = "";
        }
        return $text;
    }

    public function createPost($post) {
        if (!$post instanceof TelegramPost) {
            throw new \Exception("Неверный объект поста");
        }
        $text = $post->getText();
        $link = $post->getLinkText();
        $media = $post->getMedia();
        if ($link != "") {
            $text.= PHP_EOL.PHP_EOL.$link;
        }
        $method = "sendMessage";
        $params = [
            'text' => $text,
        ];
        if (count($media) > 0) {
            $first_media_item = array_shift($media);
            $request_params = $this->getRequestParamsForMedia($first_media_item, $post);
            $method = $request_params['method'];
            $params = $request_params['params'];
        }
        $response = $this->request($method, $params);
        $post_id = $response->result->message_id;
        if (count($media) > 0) {
            $post_ids = [$post_id];
            foreach ($media as $media_item) {
                $request_params = $this->getRequestParamsForMedia($media_item, null);
                $method = $request_params['method'];
                $params = $request_params['params'];
                $response = $this->request($method, $params);
                $post_ids[] = $response->result->message_id;
            }
            return implode(";", $post_ids);
        }
        return $post_id;
    }



    public function editPost($post_ids, $post) {
        if (!$post instanceof TelegramPost) {
            throw new \Exception("Неверный объект поста");
        }
        $text = $this->getPostText($post);
        $media = $post->getMedia();
        $post_ids = explode(";", $post_ids);
        $need_update_media = $post->needUpdateField('media');

        $first_media_item = array_shift($media);
        $need_update_first_item = null;
        if (is_array($need_update_media) && count($need_update_media) > 0) {
            $need_update_first_item = $need_update_media[0];
            if ($need_update_first_item) {
                $text = $this->getPostText($post, $first_media_item);
                if (!isset($post_ids[0]) || $post_ids[0] == "") {
                    $request_params = $this->getRequestParamsForMedia($first_media_item, $post);
                    $method = $request_params['method'];
                    $params = $request_params['params'];
                    $response = $this->request($method, $params);
                    $post_ids[] = $response->result->message_id;
                } else {
                    if ($first_media_item['type'] == "picture") {
                        $this->request("editMessageCaption", [
                            'message_id' => $post_ids[0],
                            'caption' => $text
                        ]);
                        $this->request("editMessageMedia", [
                            'message_id' => $post_ids[0],
                            'media' => [
                                'type' => 'photo',
                                'media' => $this->getPictureFullUrl($first_media_item['value'])
                            ]
                        ]);
                    } else {
                        $text = $this->getPostText($post, $first_media_item);
                        $this->request("editMessageText", [
                            'message_id' => $post_ids[0],
                            'text' => $text
                        ]);
                    }
                }
            }
        }
        if (!$need_update_first_item) {
            if ($post->needUpdateField('text') || $post->needUpdateField('link')) {
                $this->request("editMessageText", [
                    'message_id' => $post_ids[0],
                    'text' => $text
                ]);
            }
        }
        if (count($need_update_media) > 1) {
            for ($i = 1; $i < count($need_update_media); $i++) {
                if ($need_update_media[$i]) {
                     if (isset($media[$i - 1])) {
                        if (isset($post_ids[$i])) {
                            $text = $this->getPostText(null, $media[$i - 1]);
                            $this->request("editMessageCaption", [
                                'message_id' => $post_ids[$i],
                                'caption' => $text
                            ]);
                            if ($media[$i - 1]['type'] == "picture") {
                                $this->request("editMessageMedia", [
                                    'message_id' => $post_ids[$i],
                                    'media' => [
                                        'type' => 'photo',
                                        'media' => $this->getPictureFullUrl($media[$i - 1]['value'])
                                    ]
                                ]);
                            }
                        } else {
                            $request_params = $this->getRequestParamsForMedia($media[$i - 1], null);
                            $method = $request_params['method'];
                            $params = $request_params['params'];
                            $response = $this->request($method, $params);
                            $message_ids[] = $response->result->message_id;
                        }
                    } else {
                        $post_to_delete_id = $post_ids[$i - 1];
                        $response = $this->request("deleteMessage", [
                            'message_id' => $post_to_delete_id,
                        ]);
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
        foreach ($post_ids as $id) {
            $response = $this->request("deleteMessage", [
                'message_id' => $id,
            ]);
        }
        return $post_ids;
    }
    public function makeLinks($post_ids) {
        $post_ids = explode(";", $post_ids);
        $channel_name = $this->settings_manager->get('channel_name');
        if (!$channel_name) {
            return [];
        }
        $list = [];
        foreach ($post_ids as $post_id) {
            $list[] = "https://t.me/".$channel_name."/".$post_id;
        }
        return $list;
    }

}
