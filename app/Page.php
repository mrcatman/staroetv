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
}
