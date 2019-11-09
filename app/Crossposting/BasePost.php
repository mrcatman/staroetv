<?php

namespace App\Crossposting;

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

    public function doNotChangeText() {
        $this->do_not_change['text'] = true;
    }

    public function doNotChangeLink() {
        $this->do_not_change['link'] = true;
    }

    public function doNotChangePicture() {
        $this->do_not_change['picture'] = true;
    }


}