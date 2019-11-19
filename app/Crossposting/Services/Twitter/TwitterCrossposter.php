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
        $text = $post->getText();
        $link = $post->getLink();
        $picture = $post->getPicture();
        $picture_id = null;
        if ($picture != "") {
            $picture_id = $this->uploadPicture($picture);

        }
        $length = self::TWEET_LENGTH;
        if ($link != "") {
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
        if ($link) {
            $text.= " ".$link;
        }
        $data = ["status" => $text];
        if ($picture_id) {
            $data['media_ids'] = $picture_id;
        }
        $response = $connection->post("statuses/update", $data);
        return $response->id;
    }

    public function editPost($id, $post) {
        throw new \Exception("Невозможно редактировать твиты");

    }

    public function deletePost($id) {
        $connection = $this->getConnection();
        $connection->post("statuses/destroy", ["id" => $id]);
        return $id;
    }

    public function makeLinkById($post_id) {
        $screen_name = $this->settings_manager->get('screen_name');
        if (!$screen_name) {
            return null;
        }
        return "https://twitter.com/".$screen_name."/status/".$post_id;
    }

}
