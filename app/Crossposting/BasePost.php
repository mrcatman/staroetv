<?php

namespace App\Crossposting;

use Illuminate\Support\Facades\URL;

class BasePost {

    protected $text = "";
    protected $link = "";
    protected $media = [];
    protected $media_cache = [];
    protected $fields_to_update = [];

    public function getText() {
        return $this->text;
    }

    public function setText($text) {
        $this->text = $text;
        return $this;
    }

    public function getLink() {
        return $this->link;
    }

    public function getLinkValue() {
        if (is_array($this->link)) {
            return $this->link[0];
        } else {
            return $this->link;
        }
    }

    public function getLinkText() {
        if (is_array($this->link)) {
            return $this->link[1].PHP_EOL.$this->link[0];
        } else {
            return $this->link;
        }
    }

    public function setLink($link) {
        $this->link = $link;
        return $this;
    }

    public function getMedia() {
        return $this->media;
    }

    public function setMedia($media) {
        $this->media = $media;
        return $this;
    }

    public function setFieldsToUpdate($fields = []) {
        $this->fields_to_update = $fields;
    }

    public function needUpdateField($field) {
        if (isset($this->fields_to_update[$field])) {
            return $this->fields_to_update[$field];
        }
        return false;
    }

    public function getMediaCache() {
        return $this->media_cache;
    }

    public function setMediaCache($cache) {
        $this->media_cache = $cache;
    }

    public function setMediaCacheForUrl($url, $value) {
        $this->media_cache[$url] = $value;
    }

    public function getMediaCacheForUrl($url) {
        if (isset($this->media_cache[$url])) {
            return $this->media_cache[$url];
        }
        return null;
    }



}
