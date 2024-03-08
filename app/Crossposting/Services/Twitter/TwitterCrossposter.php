<?php

namespace App\Crossposting\Services\Twitter;

use App\Crossposting\BaseCrossposter;
use Abraham\TwitterOAuth\TwitterOAuth;

class TwitterCrossposter extends BaseCrossposter {

    public $id = "twitter";
    public $public_name = "Twitter";
    public $can_auto_connect = true;
    public $can_edit_posts = false;

    public const TWEET_LENGTH = 280;
    public const LINK_LENGTH = 23;

    protected $base_url = "https://api.twitter.com/";

    public function __construct() {
        parent::__construct();
        $this->settings_manager = new TwitterSettingsManager($this);
    }

    public function getPostInstance() {
        return new TwitterPost();
    }

    private function getBaseConnection() {
        $consumer_key = $this->settings_manager->get('oauth_consumer_key');
        $consumer_secret = $this->settings_manager->get('oauth_consumer_secret');
        if (!$consumer_key || !$consumer_secret) {
            throw new \Exception("Не указан consumer key или consumer secret");
        }
        $connection = new TwitterOAuth($consumer_key, $consumer_secret);
        return $connection;
    }

    private function getConnection() {
        $consumer_key = $this->settings_manager->get('oauth_consumer_key');
        $consumer_secret = $this->settings_manager->get('oauth_consumer_secret');
        $oauth_token = $this->settings_manager->get('oauth_token');
        $oauth_token_secret = $this->settings_manager->get('oauth_token_secret');
        if (!$consumer_key || !$consumer_secret || !$oauth_token || !$oauth_token_secret) {
            throw new \Exception("Не указаны все токены авторизации");
        }
        $connection = new TwitterOAuth($consumer_key, $consumer_secret, $oauth_token, $oauth_token_secret);
        return $connection;
    }

    public function getAutoConnectRedirectURI() {
        $callback = route("crosspostRedirectUri", ["name" => "twitter"]);
        $connection = $this->getBaseConnection();
        $temporary_credentials = $connection->oauth('oauth/request_token',["oauth_callback" => $callback]);
        $url = $connection->url('oauth/authenticate', array('oauth_token' => $temporary_credentials['oauth_token']));
        return $url;
    }

    public function afterRedirect($data) {
        $connection = $this->getBaseConnection();
        $params = array("oauth_verifier" => $data['oauth_verifier'], 'oauth_token' => $data['oauth_token']);
        $user_data = $connection->oauth('oauth/access_token', $params);
        foreach ($user_data as $key => $value) {
            $this->settings_manager->set($key, $value);
        }
        $this->settings_manager->saveSettingsToFile();
    }

    public function isActive() {
        $token = $this->settings_manager->get("oauth_token");
        return (bool)$token;
    }

    protected function uploadPicture($picture) {
        $connection = $this->getConnection();
        if ($picture[0] == "/") {
            $picture = public_path($picture);
        } else {
            $rnd = md5(random_bytes(16));
            $path = public_path("pictures/temp/".$rnd);
            file_put_contents($path, file_get_contents($picture));
            $picture = $path;
        }
        $upload = $connection->upload('media/upload', ['media' => $picture]);
        return $upload->media_id;
    }


    public function createPost($post) {
        if (!$post instanceof TwitterPost) {
            throw new \Exception("Неверный объект поста");
        }
        $connection = $this->getConnection();
        $post_text = $post->getText();
        $link = $post->getLinkText();
        $media = $post->getMedia();
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
            dd($text, $post);
            $text = substr($text, 0, strpos($text, "\n"));
            if (substr($text, -strlen($text_end)) != $text_end) {
                $text .= $text_end;
            }
        }
        if ($first_media_is_video) {
            $text.= PHP_EOL.$media[0]['value'];
        } elseif(is_array($post_text) && count($post_text) > 0) {

        } elseif ($link) {
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

    public function deletePost($post_ids) {
        $connection = $this->getConnection();
        $post_ids = explode(";", $post_ids);
        foreach ($post_ids as $post_id) {
            $connection->post("statuses/destroy", ["id" => $post_id]);
        }
        return $post_ids;
    }

    public function makeLinks($post_ids) {
        $post_ids = explode(";", $post_ids);
        $screen_name = $this->settings_manager->get('screen_name');
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
