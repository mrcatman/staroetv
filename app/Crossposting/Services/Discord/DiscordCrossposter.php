<?php

namespace App\Crossposting\Services\Discord;

use App\Crossposting\Crossposter;
use App\Crossposting\Post;
use RestCord\DiscordClient;

class DiscordCrossposter extends Crossposter {

    protected $params = [
        'id' => 'discord',
        'public_name' => 'Discord',
        'can_auto_connect' => false,
        'can_edit_posts' => true,
        'can_edit_comments' => true,
    ];
    private $client;

    public function __construct() {
        $this->config = new DiscordConfigManager($this);
        if ($this->config->get('bot_token')) {
            $this->client = new DiscordClient(['token' => $this->config->get('bot_token')]);
        }
    }

    public function getPostInstance(): Post {
        return new DiscordPost();
    }

    public function isActive(): bool {
        return (bool)$this->config->get("bot_token");;
    }


    public function createPost(Post $post): string {
        if (!$post instanceof DiscordPost) {
            throw new \Exception("Неверный объект поста");
        }

        $post_ids = [];

        $media = $post->getMedia();
        $params = $this->getRequestParams($post, count($media) > 0 ? $media[0] : null);
        $response = $this->client->channel->createMessage($params);
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

    private function getRequestParams(?DiscordPost $post, $media = null) {
        if ($post) {
            $text = $post->getParam('text');
            $link = $post->getParam('link');
            if ($link != "") {
                $text.= PHP_EOL.PHP_EOL.$link;
            }
        } else {
            $text = isset($media['text']) && $media['text'] != "" ? $media['text'] : "...";
        }
        $params = [
            'content' => $text,
            'channel.id' => (int)$this->config->get('group_id'),
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

    public function editPost(int|string $id, Post $post) {
        if (!$post instanceof DiscordPost) {
            throw new \Exception("Неверный объект поста");
        }

        $post_ids = explode(";", $id);

        $media = $post->getParam('media');
        $need_update_media = $post->needUpdateField('media');

        if ($post->needUpdateField('text') || $post->needUpdateField('link') || count($need_update_media) > 0 && $need_update_media[0]) {
            $params = $this->getRequestParams($post, count($media) > 0 ? $media[0] : null);
            $params['message.id'] = (int)$post_ids[0];
            $this->client->channel->editMessage($params);
        }
        if (count($need_update_media) > 1) {
            for ($i = 1; $i < count($need_update_media); $i++) {
                if ($need_update_media[$i]) {
                    if (isset($media[$i])) {
                        $params = $this->getRequestParams(null, $media[$i]);
                        if (isset($post_ids[$i])) {
                            $params['message.id'] = (int)$post_ids[$i];
                            $this->client->channel->editMessage($params);
                        } else {
                            $response = $this->client->channel->createMessage($params);
                            $post_ids[] = $response->toArray()['id'];
                        }
                    } else {
                        $post_to_delete_id = $post_ids[$i];
                        try {
                            $this->client->channel->deleteMessage([
                                'channel.id' => (int)$this->config->get('group_id'),
                                'message.id' => (int)$post_ids[$i]
                            ]);
                        }  catch (\Exception $e) {}

                        $post_ids = array_filter($post_ids, function($post_id) use ($post_to_delete_id) {
                            return $post_id != $post_to_delete_id;
                        });
                    }
                }
            }
        }
        return implode(";", $post_ids);
    }

    public function deletePost(int|string $id): void {
        $post_ids = explode(";", $id);
        foreach ($post_ids as $post_id) {
            try {
                $this->client->channel->deleteMessage([
                    'channel.id' => (int)$this->client->get('group_id'),
                    'message.id' => (int)$post_id
                ]);
            } catch (\Exception $e) {}
        }
    }

}
