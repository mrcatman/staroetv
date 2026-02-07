<?php

namespace App\Crossposting\Services\Twitter;

use App\Crossposting\Crossposter;
use Abraham\TwitterOAuth\TwitterOAuth;
use App\Crossposting\Post;

class TwitterCrossposter extends Crossposter {

    protected $params = [
        'id' => 'twitter',
        'public_name' => 'Twitter',
        'can_auto_connect' => false,
        'can_edit_posts' => false,
        'can_edit_comments' => false,
    ];
    public const TWEET_LENGTH = 280;
    public const LINK_LENGTH = 23;

    public function __construct() {
        $this->config = new TwitterConfigManager($this);
    }

    public function getPostInstance(): Post {
        return new TwitterPost();
    }

    private function getBaseConnection(): TwitterOAuth{
        $consumer_key = $this->config->get('oauth_consumer_key');
        $consumer_secret = $this->config->get('oauth_consumer_secret');
        if (!$consumer_key || !$consumer_secret) {
            throw new \Exception("Не указан consumer key или consumer secret");
        }
        return new TwitterOAuth($consumer_key, $consumer_secret);
    }

    private function getConnection(): TwitterOAuth {
        $consumer_key = $this->config->get('oauth_consumer_key');
        $consumer_secret = $this->config->get('oauth_consumer_secret');
        $oauth_token = $this->config->get('oauth_token');
        $oauth_token_secret = $this->config->get('oauth_token_secret');
        if (!$consumer_key || !$consumer_secret || !$oauth_token || !$oauth_token_secret) {
            throw new \Exception("Не указаны все токены авторизации");
        }
        return new TwitterOAuth($consumer_key, $consumer_secret, $oauth_token, $oauth_token_secret);
    }

    public function getAutoConnectRedirectURI(): string {
        $callback = route("crossposts.redirect-uri", ["name" => "twitter"]);
        $connection = $this->getBaseConnection();
        $temporary_credentials = $connection->oauth('oauth/request_token',["oauth_callback" => $callback]);
        return $connection->url('oauth/authenticate', array('oauth_token' => $temporary_credentials['oauth_token']));
    }

    public function afterRedirect($data): void {
        $connection = $this->getBaseConnection();
        $params = ["oauth_verifier" => $data['oauth_verifier'], 'oauth_token' => $data['oauth_token']];
        $user_data = $connection->oauth('oauth/access_token', $params);

        foreach ($user_data as $key => $value) {
            $this->config->set($key, $value);
        }
        $this->config->saveSettingsToFile();
    }

    public function isActive(): bool {
        return !!$this->config->get("oauth_token");
    }

    private function uploadPicture(string $path) {
        $connection = $this->getConnection();
        if ($path[0] == "/") {
            $path = public_path($path);
        } else {
            $rnd = md5(random_bytes(16));
            $path = public_path("pictures/temp/".$rnd);
            file_put_contents($path, file_get_contents($path));
        }
        $upload = $connection->upload('media/upload', ['media' => $path]);
        return $upload->media_id;
    }


    public function createPost(Post $post) {
        if (!$post instanceof TwitterPost) {
            throw new \Exception("Неверный объект поста");
        }

        $connection = $this->getConnection();

        $post_text = $post->getParam('text');
        $link = $post->getParam('link_text');
        $media = $post->getParam('media');

        if (is_array($post_text)) {
           $text = array_shift($post_text);
        } else {
           $text = $post_text;
        }
        $text = trim($text);
        $first_media_is_video = count($media) > 0 && $media[0]['type'] == "video";
        $first_media_is_picture = count($media) > 0 && $media[0]['type'] == "picture";
        $length = self::TWEET_LENGTH;
        if ($link != "" || $first_media_is_video) {
            $length = $length - self::LINK_LENGTH - 2;
        }
        if (mb_strlen($text, "UTF-8") > $length) {
            $text_end = "...";
            $text = wordwrap($text, $length - strlen($text_end));
            $text = substr($text, 0, strpos($text, "\n"));
            if (substr($text, -strlen($text_end)) != $text_end) {
                $text .= $text_end;
            }
        }
        if ($first_media_is_video) {
            $text.= PHP_EOL.$media[0]['value'];
        } elseif (is_array($post_text) && count($post_text) > 0) {} elseif ($link) {
            $text.= PHP_EOL.$link;
        }
        $data = ["status" => $text];
        if ($first_media_is_picture) {
            $picture_id = $this->uploadPicture($media[0]['value']);
            $data['media_ids'] = $picture_id;
        }
        $response = $connection->post("statuses/update", $data);
        $post_ids = [$response->id];
        if (is_array($post_text) && count($post_text) > 0) {
            $index = 0;
            foreach ($post_text as $additional_text) {
                $data = [
                    "status" => $additional_text,
                    'in_reply_to_status_id' => $post_ids[count($post_ids) - 1],
                    'auto_populate_reply_metadata' => true
                ];
                if ($index == count($post_text) - 1) {
                    if ($link) {
                        $data['status'] = $additional_text.PHP_EOL.$link;
                    }
                }
                usleep(500000);
                $response = $connection->post("statuses/update", $data);
                $post_ids[] = $response->id;
                $index++;
            }
        }
        if (count($media) > 1) {
            for ($i = 1; $i < count($media); $i++) {
                $media_item = $media[$i];
               $text = isset($media_item['text']) && $media_item['text'] != "" ? $media_item['text'] : "...";
                if ($media_item['type'] == "picture") {

                    $picture_id = $this->uploadPicture($media_item['value']);
                    $data = [
                        'status' => $text,
                        'media_ids' => $picture_id,
                        'in_reply_to_status_id' => $post_ids[count($post_ids) - 1],
                        'auto_populate_reply_metadata' => true
                    ];
                    usleep(500000);
                    $response = $connection->post("statuses/update", $data);
                    $post_ids[] = $response->id;
                } elseif ($media_item['type'] == "video") {
                    $length = self::TWEET_LENGTH - self::LINK_LENGTH - 2;
                    if (mb_strlen($text, "UTF-8") > $length) {
                        $text_end = "...";
                        $text = wordwrap($text, $length - strlen($text_end));
                        $text = substr($text, 0, strpos($text, "\n"));
                        if (substr($text, -strlen($text_end)) != $text_end) {
                            $text .= $text_end;
                        }
                    }
                    $text.= " ".$media_item['value'];
                    $data = [
                        'status' => $text,
                        'in_reply_to_status_id' => $post_ids[count($post_ids) - 1],
                        'auto_populate_reply_metadata' => true
                    ];
                    usleep(500000);
                    $response = $connection->post("statuses/update", $data);
                    $post_ids[] = $response->id;
                }
            }
        }
        if ($first_media_is_video && $link) {
            $data = [
                'status' => $link,
                'in_reply_to_status_id' => $post_ids[count($post_ids) - 1],
                'auto_populate_reply_metadata' => true
            ];
            $response = $connection->post("statuses/update", $data);
            $post_ids[] = $response->id;
        }
        return implode(";", $post_ids);
    }

    public function editPost($id, $post) {
        throw new \Exception("Невозможно редактировать твиты");

    }

    public function deletePost(int|string $id): void {
        $connection = $this->getConnection();
        $post_ids = explode(";", $id);
        foreach ($post_ids as $post_id) {
            $connection->post("statuses/destroy", ["id" => $post_id]);
        }
    }

    public function makeLinks(int|string $id): array {
        $post_ids = explode(";", $id);
        $screen_name = $this->config->get('screen_name');
        if (!$screen_name) {
            return [];
        }
        $list = [];
        foreach ($post_ids as $post_id) {
            $list[] = "https://twitter.com/".$screen_name."/status/".$post_id;
        }
        return $list;
    }

}
