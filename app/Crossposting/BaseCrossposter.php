<?php

namespace App\Crossposting;

class BaseCrossposter {

    public $name = null;
    public $public_name = null;
    public $can_auto_connect = true;
    public $can_edit_posts = true;

    public function __construct() {
        $this->client = new \GuzzleHttp\Client(['verify' => false]);
        $this->settings_manager = new BaseSettingsManager($this);
    }

    public function getPostInstance() {
        return new BasePost();
    }

    public function isActive() {
        return false;
    }


    public function getAutoConnectRedirectURI() {

    }

    public function afterRedirect($data) {

    }


    public function createPost($data) {

    }

    public function editPost($id, $data) {

    }

    public function deletePost($id) {

    }

    public function makeLinkById($id) {

    }

}
