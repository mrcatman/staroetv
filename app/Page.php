<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class Page extends Model {

    public $table = "static_pages";
    protected $guarded = [];

    public function getContentAttribute() {
        $content = $this->attributes['content'];
        $content = str_replace("\ ", "", $content);
        return $content;
    }

    public function getFullUrlAttribute() {
        if ($this->url) {
            return "/pages/".$this->url;
        } else {
            return "/index/0-".$this->id;
        }
    }
}
