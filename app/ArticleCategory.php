<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class ArticleCategory extends Model {

    public $table = "articles_categories";
    protected $guarded = [];

    public function getFullUrlAttribute() {
        $url = "";
        if ($this->url) {
            $url = "category/".$this->url;
        } else {
            $url = "category/".$this->id;
        }
        if ($this->type_id == Article::TYPE_NEWS) {
            $path = "/news/".$url;
            return $path;
        }
        if ($this->type_id == Article::TYPE_ARTICLES) {
            $path = "/articles/".$url;
            return $path;
        }
        if ($this->type_id == Article::TYPE_BLOG) {
            $path = "/blog/".$url;
            return $path;
        }

    }
}
