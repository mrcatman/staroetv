<?php

namespace App\Crossposting\Services\Telegram;

use App\Crossposting\Crossposter;
use App\Crossposting\Post;
use Illuminate\Support\Facades\Http;

class TelegramCrossposter extends Crossposter {

    protected $params = [
        'id' => 'telegram',
        'public_name' => 'Telegram',
        'can_auto_connect' => false,
        'can_edit_posts' => true,
        'can_edit_comments' => false,
    ];

    public function __construct() {
        $this->config = new TelegramConfigManager($this);
    }

    public function getPostInstance(): Post {
        return new TelegramPost();
    }

    public function isActive(): bool {
        return !!$this->config->get("access_token");
    }

    public function request($url, $params)
    {
        $token = $this->config->get("access_token");
        if (!$token) {
            throw new \Exception("Не указан токен");
        }
        $group_id = $this->config->get("group_id");
        if (!$group_id) {
            throw new \Exception("Не указан id группы");
        }
        $params['chat_id'] = $group_id;
        $params['parse_mode'] = "html";

        return Http::post('https://api.telegram.org/bot' . $token . "/" . $url, $params)->json();
    }

    protected function getRequestParamsForMedia($media_item, ?Post $post = null) {
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


    protected function getPostText(Post $post = null, $media_item = null): string {
        if (!$media_item && !$post) {
            return "";
        }
        if ($media_item && isset($media_item['text']) && $media_item['text'] != "") {
            $text = $media_item['text'];
            if ($media_item['type'] == "video") {
                $text.= PHP_EOL.$media_item['value'];
            }
        } elseif ($post) {
            $text = $post->getParam('text');
            if ($media_item && $media_item['type'] == "video") {
                $text .= PHP_EOL;
                $text .= $media_item['value'];
            }
            $link = $post->getParam('link_text');
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

    public function createPost(Post $post) {
        if (!$post instanceof TelegramPost) {
            throw new \Exception("Неверный объект поста");
        }
        $text = $post->getParam('text');
        $link = $post->getParam('link_text');
        $media = $post->getParam('media');

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



    public function editPost(int|string $id, Post $post) {
        if (!$post instanceof TelegramPost) {
            throw new \Exception("Неверный объект поста");
        }
        $text = $this->getPostText($post);

        $media = $post->getParam('media');
        $id = explode(";", $id);
        $need_update_media = $post->needUpdateField('media');

        $first_media_item = array_shift($media);
        $need_update_first_item = null;
        if (is_array($need_update_media) && count($need_update_media) > 0) {
            $need_update_first_item = $need_update_media[0];
            if ($need_update_first_item) {
                $text = $this->getPostText($post, $first_media_item);
                if (!isset($id[0]) || $id[0] == "") {
                    $request_params = $this->getRequestParamsForMedia($first_media_item, $post);
                    $method = $request_params['method'];
                    $params = $request_params['params'];
                    $response = $this->request($method, $params);
                    $id[] = $response->result->message_id;
                } else {
                    if ($first_media_item['type'] == "picture") {
                        $this->request("editMessageCaption", [
                            'message_id' => $id[0],
                            'caption' => $text
                        ]);
                        $this->request("editMessageMedia", [
                            'message_id' => $id[0],
                            'media' => [
                                'type' => 'photo',
                                'media' => $this->getPictureFullUrl($first_media_item['value'])
                            ]
                        ]);
                    } else {
                        $text = $this->getPostText($post, $first_media_item);
                        $this->request("editMessageText", [
                            'message_id' => $id[0],
                            'text' => $text
                        ]);
                    }
                }
            }
        }
        if (!$need_update_first_item) {
            if ($post->needUpdateField('text') || $post->needUpdateField('link')) {
                $this->request("editMessageText", [
                    'message_id' => $id[0],
                    'text' => $text
                ]);
            }
        }
        if (count($need_update_media) > 1) {
            for ($i = 1; $i < count($need_update_media); $i++) {
                if ($need_update_media[$i]) {
                     if (isset($media[$i - 1])) {
                        if (isset($id[$i])) {
                            $text = $this->getPostText(null, $media[$i - 1]);
                            $this->request("editMessageCaption", [
                                'message_id' => $id[$i],
                                'caption' => $text
                            ]);
                            if ($media[$i - 1]['type'] == "picture") {
                                $this->request("editMessageMedia", [
                                    'message_id' => $id[$i],
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
                            $id[] = $response->result->message_id;
                        }
                    } else {
                        $post_to_delete_id = $id[$i - 1];
                        $response = $this->request("deleteMessage", [
                            'message_id' => $post_to_delete_id,
                        ]);
                        $id = array_filter($id, function($post_id) use ($post_to_delete_id) {
                            return $post_id != $post_to_delete_id;
                        });
                    }
                }
            }
        }
        return implode(";", $id);
    }

    public function deletePost(int|string $id): void {
        $post_ids = explode(";", $id);
        foreach ($post_ids as $id) {
           $this->request("deleteMessage", [
                'message_id' => $id,
            ]);
        }
    }
    public function makeLinks(string $id): array {
        $channel_name = $this->config->get('channel_name');
        if (!$channel_name) {
            return [];
        }

        $post_ids = explode(";", $id);
        $list = [];
        foreach ($post_ids as $post_id) {
            $list[] = "https://t.me/".$channel_name."/".$post_id;
        }
        return $list;
    }

}
