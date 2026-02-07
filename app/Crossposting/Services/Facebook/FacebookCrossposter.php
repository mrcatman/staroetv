<?php

namespace App\Crossposting\Services\Facebook;

use App\Crossposting\Crossposter;
use App\Crossposting\Post;
use Illuminate\Support\Facades\Http;

class FacebookCrossposter extends Crossposter {

    protected $params = [
        'id' => 'facebook',
        'public_name' => 'Facebook',
        'can_auto_connect' => false,
        'can_edit_posts' => false,
        'can_edit_comments' => false,
    ];

    public function __construct() {;
        $this->config = new FacebookConfigManager($this);
    }

    public function getPostInstance(): Post{
        return new FacebookPost();
    }

    public function isActive(): bool {
        return (bool)$this->config->get("ifttt_key");
    }


    private function request($params) {
        $key = $this->config->get("ifttt_key");
        if (!$key) {
            throw new \Exception("Не указан ключ");
        }
        $event = $this->config->get("ifttt_event");
        if (!$event) {
            throw new \Exception("Не указано название события");
        }
        $url = "https://maker.ifttt.com/trigger/$event/with/key/$key";

        return Http::post($url, $params);
    }


    private function getRequestParams(?FacebookPost $post, $media = null) {
        if ($post) {
            $text = $post->getParam('text');
            $link = $post->getParam('link');
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
        if ($post && $post->getParam('link_value')) {
            $text.= PHP_EOL.$post->getParam('link_text');
            $params['value2'] = $text;
            $params['value1'] = $post->getParam('link_value');
        } elseif ($media) {
            $params['value1'] = $media['value'];
        }
        return $params;
    }

    public function createPost(Post $post): string {
        if (!$post instanceof FacebookPost) {
            throw new \Exception("Неверный объект поста");
        }

        $media = $post->getParam('media');
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

    public function editPost(int|string $id, Post $post) {
        throw new \Exception("Невозможно редактировать посты, внесите изменения вручную");
    }

    public function deletePost(int|string $id) {
        throw new \Exception("Невозможно удалять посты, сделайте это вручную");
    }

}
