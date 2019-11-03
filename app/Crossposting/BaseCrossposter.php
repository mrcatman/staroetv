<?php

namespace App\Crossposting;

class BaseCrossposter {

    public $name = null;
    public $public_name = null;

    protected $params = [];
    protected $settings = null;

    public $can_auto_connect = true;
    protected $can_edit_post = true;

    public function getAutoConnectRedirectURI() {

    }

    public function afterRedirect() {

    }


    public function createPost($data) {

    }

    public function editPost($id, $data) {

    }

    public function deletePost($id) {

    }

    protected function loadSettings() {
        if (file_exists(app_path("Crossposting/config/".$this->name.".json"))) {
            $settings = file_get_contents(app_path("Crossposting/config/".$this->name.".json"));
            $settings = json_decode($settings);
            if ($settings) {
                $this->settings = $settings;
            } else {
                $this->settings = [];
            }
        } else {
            $this->settings = [];
        }
    }

    protected function saveSettings() {
        file_put_contents(app_path("Crossposting/config/".$this->name.".json"), json_encode($this->settings));
    }

    protected function getSetting($key) {
        if ($this->settings === null) {
            $this->loadSettings();
        }

        return isset($this->settings->{$key}) ? $this->settings->{$key} : null;
    }

    protected function saveSetting($key, $value) {
        if ($this->settings === null) {
            $this->loadSettings();
        }
        $this->settings->{$key} = $value;
        $this->saveSettings();
    }
}