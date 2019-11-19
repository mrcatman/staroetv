<?php

namespace App;
use App\Helpers\DatesHelper;
use Illuminate\Database\Eloquent\Model;

class Article extends Model {

    protected $guarded = [];
    const TYPE_ARTICLES = 1;
    const TYPE_NEWS = 2;
    const TYPE_BLOG = 8;

    const names = [
        self::TYPE_ARTICLES => 'articles',
        self::TYPE_NEWS => 'news',
        self::TYPE_BLOG => 'blog'
    ];

    public function getTitleAttribute() {
        return html_entity_decode($this->attributes['title']);
    }

    public function getShortContentAttribute() {
        if ($this->attributes['short_content'] != "") {
            return html_entity_decode($this->attributes['short_content']);
        }
        $limit = 300;
        $content = $this->attributes['content'];
        $content = strip_tags($content);
        $content = html_entity_decode($content);
        $sentences = explode(". ", $content);
        $text = "";
        $i = 0;
        $total_length = 0;

        while ($total_length < $limit && isset($sentences[$i])) {
            $text.= $sentences[$i].". ";
            $total_length += mb_strlen($sentences[$i].". ", "UTF-8");
            $i++;
        }
        return $text;

    }

    public function getContentAttribute() {
        $content = $this->attributes['content'];
        $content = str_replace("&nbsp;", " ", $content);
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
        if (!isset($this->attributes['month'])) {
            $month = date('m', $this->created_at_original);
        } else {
            $month = $this->attributes['month'];
        }

        if ($month < 10) {
            return "0".$month;
        }
        return $month;
    }

    public function getDayAttribute() {
        if (!isset($this->attributes['day'])) {
            $day = date('d', $this->created_at_original);
        } else {
            $day = $this->attributes['day'];
        }
        if ($day < 10) {
            return "0".$day;
        }
        return $day;
    }

    public function getYearAttribute() {
        if (!isset($this->attributes['year'])) {
            return date('Y', $this->created_at_original);
        } else {
            return $this->attributes['year'];
        }
    }

    public function getUrlAttribute() {
        $day = $this->day;
        $month = $this->month;
        $year = $this->year;

        if ($this->type_id == self::TYPE_NEWS) {
            $path = "/news/".$year."-".$month."-".$day."-".$this->original_id;
            return $path;
        }
        if ($this->type_id == self::TYPE_ARTICLES) {
            $path = "/blog/".$year."-".$month."-".$day."-".$this->original_id;
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

    public function getCreatedAtAttribute() {
        if (!isset($this->attributes['created_at'])) {
            return "";
        }
        return DatesHelper::format($this->attributes['created_at']);
    }

    public function getCreatedAtOriginalAttribute() {
        if (!isset($this->attributes['created_at'])) {
            return 0;
        }
        return strtotime($this->attributes['created_at']);
    }

    public function coverPicture() {
        return $this->hasOne('App\Picture', 'id', 'cover_id');
    }

    public function crossposts() {
        return $this->hasMany('App\Crosspost');
    }

}
