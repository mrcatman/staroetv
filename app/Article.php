<?php

namespace App;
use App\Helpers\DatesHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

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
        if (mb_strpos($content, '$CUT$', 0, 'UTF-8')  !== false) {
            return mb_substr($content, 0, mb_strpos($content, '$CUT$', 0, 'UTF-8'));
        }
        if (mb_strlen($content, "UTF-8") < $limit) {
            return $content;
        }
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

    public function searchContent($search) {
        $content = $this->attributes['content'];
        $content = strip_tags($content);
        $content = html_entity_decode($content);
        $position = mb_stripos($content, $search, 0, 'UTF-8');
        if ($position === false) {
            return $this->short_content;
        }
        $limit = 300;
        $start = $position - $limit / 2;
        if ($start < 0) {
            $start = 0;
        }
        $cut = mb_substr($content, $start, 300 + mb_strlen($search, 'UTF-8'), 'UTF-8');
        $words = explode(" ", $cut);
        if ($position > $limit / 2) {
            unset($words[0]);
        }
        if ($position < mb_strlen($content, 'UTF-8') - $limit / 2) {
            unset($words[count($words)]);
        }
        $cut = implode(" ", $words);
        //$start_replacement = '<span class="highlight">';
        //$end_replacement = '</span>';
        $string_original_case = mb_substr($content, $position, mb_strlen($search, 'UTF-8'), 'UTF-8');
        $cut = preg_replace("~$search~iu", '<span class="highlight">'.$string_original_case.'</span>', $cut);
        if ($position > $limit / 2) {
            $cut = "...".$cut;
        }
        if ($position < mb_strlen($content, 'UTF-8') - $limit / 2) {
            $cut = $cut."...";
        }
        return $cut;
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

    public function getFixedContentAttribute() {
        $content = $this->attributes['content'];
        $content = str_replace("&nbsp;", " ", $content);
        $content = preg_replace("/\s+/", " ", $content);
        $content = str_replace("<br><br>", "<br>", $content);
        $content = str_replace("<br><br>", "<br>", $content);
        $content = str_replace("<br /><br><br /><br>", "<br>", $content);
        $content = trim($content);
        $dom = new \DOMDocument;
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $wrapper = $dom->createElement('div');
        $wrapper->setAttribute('class','certain-ratio-wrapper');

        $iframes = $dom->getElementsByTagName('iframe');
        foreach ($iframes as $iframe) {
            $width = $iframe->getAttribute('width');
            $height = $iframe->getAttribute('height');
            if ($width && $height) {
                $ratio = $height / $width * 100;
                $iframe->removeAttribute('width');
                $iframe->removeAttribute('height');
                $wrapper_clone = $wrapper->cloneNode();
                $wrapper_clone->setAttribute('style', "padding-top: $ratio%");
                $iframe->parentNode->replaceChild($wrapper_clone, $iframe);
                $wrapper_clone->appendChild($iframe);
            }
        }
        return html_entity_decode($dom->saveHTML());
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
        $month = (int)$month;
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
        $day = (int)$day;
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

    public function getCoverUrlAttribute() {
        $url = null;
        if ($this->cover) {
            $url = $this->cover;
        }
        if ($this->coverPicture) {
            $url = $this->coverPicture->url;
        }
        if ($url == "http://staroetv.su/img/noobl2.jpg" || $url == "/img/noobl2.jpg") {
            $url = null;
        }
        return $url;
    }

    public function crossposts() {
        return $this->hasMany('App\Crosspost');
    }

    public function getCommentsCountAttribute() {
       return Cache::remember("comments_count_articles_".$this->id, 3600 * 24, function () {
           return count($this->comments);
       });
    }

}
