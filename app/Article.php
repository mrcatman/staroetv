<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class Article extends Model {

    protected $guarded = [];
    const TYPE_ARTICLES = 1;
    const TYPE_NEWS = 2;
    const TYPE_BLOG = 8;

    public function getTitleAttribute() {
        return html_entity_decode($this->attributes['title']);
    }

    public function getShortContentAttribute() {
        if ($this->attributes['short_content'] != "") {
            return strip_tags($this->attributes['short_content']);
        }
        $limit = 500;
        $content = $this->attributes['content'];
        $content = strip_tags($content);
        return \Illuminate\Support\Str::limit($content, $limit, "...");
    }

    public function getContentAttribute() {
        $content = $this->attributes['content'];
        $content = str_replace("&nbsp;", "", $content);
        $content = preg_replace("/\s+/", " ", $content);
        $content = str_replace("<br><br>", "<br>", $content);
        $content = str_replace("<br><br>", "<br>", $content);
        $content = str_replace("<br /><br><br /><br>", "<br>", $content);
        $content = trim($content);
        return $content;
    }

    public function comments() {
        return $this->hasMany('App\Comment', 'material_id', 'original_id')->where(['material_type' => $this->type_id]);
    }

    public function getMonthAttribute() {
        $month = $this->attributes['month'];
        if ($month < 10) {
            return "0".$month;
        }
        return $month;
    }

    public function getDayAttribute() {
        $day = $this->attributes['day'];
        if ($day < 10) {
            return "0".$day;
        }
        return $day;
    }

    public function getUrlAttribute() {
        if ($this->type_id == self::TYPE_NEWS) {
            $path = "/news/".$this->year."-".$this->month."-".$this->day."-".$this->original_id;
            return $path;
        }
        if ($this->type_id == self::TYPE_BLOG) {
            $path = "/stuff/".$this->category_id."-1-0-".$this->original_id;
            return $path;
        }
    }

    public function user() {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }

}
