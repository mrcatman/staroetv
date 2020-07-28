<?php

namespace App\Crossposting;

use Illuminate\Support\Facades\URL;

class BasePost {

    protected $text = "";
    protected $link = "";
    protected $picture = "";

    protected $do_not_change = [];

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

    public function setLink($link) {
        $this->link = $link;
        return $this;
    }

    public function getPicture() {
        return $this->picture;
    }

    public function setPicture($picture) {
        $this->picture = $picture;
        return $this;
    }

    public function doNotChangeText($state = true) {
        $this->do_not_change['text'] = $state;
    }

    public function doNotChangeLink($state = true) {
        $this->do_not_change['link'] = $state;
    }

    public function doNotChangePicture($state = true) {
        $this->do_not_change['picture'] = $state;
    }

    public function needChangeText() {
        if (isset($this->do_not_change['text'])) {
            return $this->do_not_change['text'];
        }
        return true;
    }

    public function needChangeLink() {
        if (isset($this->do_not_change['link'])) {
            return $this->do_not_change['link'];
        }
        return true;
    }

    public function needChangePicture() {
        if (isset($this->do_not_change['picture'])) {
            return $this->do_not_change['picture'];
        }
        return true;
    }


}
