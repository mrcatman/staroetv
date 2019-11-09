<?php

namespace App\Crossposting;

class BaseSettingsManager {

    protected $settings = [];
    protected $settings_values = null;
    protected $file_path = "";

    public function __construct($crossposter) {
        $this->crossposter = $crossposter;
    }
    
    public function getFilePath() {
        return app_path("Crossposting/config/".$this->crossposter->id.".json");
    }

    public function getSettingsList() {
        $settings = $this->settings;
        foreach ($settings as &$setting) {
            $setting['value'] = $this->get($setting['id']);
        }
        return $this->filterSettingsBeforeGet($settings);
    }


    protected function loadSettingsFromFile() {
        if (file_exists($this->getFilePath())) {
            $settings = file_get_contents($this->getFilePath());
            $settings = json_decode($settings);
            if ($settings) {
                $this->settings_values = $settings;
            } else {
                $this->settings_values = (object)[];
            }
        } else {
            $this->settings_values = (object)[];
        }
    }

    protected function saveSettingsToFile() {
        if ($this->settings_values === null) {
            $this->loadSettingsFromFile();
        }
        file_put_contents($this->getFilePath(), json_encode($this->settings_values));
    }

    public function get($key) {
        if ($this->settings_values === null) {
            $this->loadSettingsFromFile();
        }
        return isset($this->settings_values->{$key}) ? $this->settings_values->{$key} : null;
    }

    public function set($key, $value) {
        if ($this->settings_values === null) {
            $this->loadSettingsFromFile();
        }
        $this->settings_values->{$key} = $value;
    }

    protected function filterSettingsBeforeGet($settings) {
        return $settings;
    }

    protected function filterSettingsBeforeSave($settings) {
        return $settings;
    }

    public function saveSettingsFromRequest($data) {
        foreach ($this->settings as $setting) {
            if (isset($data[$setting['id']])) {
                $this->set($setting['id'], $data[$setting['id']]);
            }
        }
        $this->saveSettingsToFile();
    }
}