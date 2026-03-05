<?php

namespace App\Models;

use App\Constants\CacheTimes;
use App\Constants\MaterialTypes;
use App\Helpers\DatesHelper;
use App\Helpers\PermissionsHelper;
use App\Helpers\RegexHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class Article extends Model {

    protected $guarded = [];

    const names = [
        MaterialTypes::TYPE_ARTICLES => 'articles',
        MaterialTypes::TYPE_NEWS => 'news',
        MaterialTypes::TYPE_BLOG => 'blog'
    ];

    public function getCanEditAttribute()
    {
        return auth()->user() && (auth()->user()->id === $this->user_id && PermissionsHelper::allows('nwodel')) || PermissionsHelper::allows('nwdel');
    }

    public function getTitleAttribute() {
        return html_entity_decode($this->attributes['title']);
    }

    public function getShortContentAttribute() {
        return Cache::remember('article_short_content_'.$this->id, CacheTimes::RELATION, function () {
            if ($this->attributes['short_content'] != "") {
                return html_entity_decode($this->attributes['short_content']);
            }

            $limit = 300;
            $content = $this->attributes['content'];
            $content = strip_tags($content);
            $content = html_entity_decode($content);
            if (mb_strpos($content, '$CUT$', 0, 'UTF-8') !== false) {
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
                $text .= $sentences[$i] . ". ";
                $total_length += mb_strlen($sentences[$i] . ". ", "UTF-8");
                $i++;
            }
            return $text;
        });
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

    public function getFixedContentAttribute()
    {
       return Cache::remember('article_fixed_content_' . $this->id, CacheTimes::RELATION, function () {
            libxml_use_internal_errors(true);
            $content = $this->attributes['content'];
            $content = str_replace("&nbsp;", " ", $content);
            $content = preg_replace("/\s+/", " ", $content);
            $content = str_replace("<br><br>", "<br>", $content);
            $content = str_replace("<br><br>", "<br>", $content);
            $content = str_replace("<br /><br><br /><br>", "<br>", $content);
            $content = trim($content);
            $content = '<div>'.$content.'</div>';
            $dom = new \DOMDocument;
            $dom->loadHTML('<?xml encoding="utf-8" ?>' . $content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

            $wrapper = $dom->createElement('div');
            $wrapper->setAttribute('class', 'certain-ratio-wrapper');

            $xpath = new \DOMXPath($dom);

            $elements = $xpath->query('//iframe | //img');

            foreach ($elements as $element) {
                $width = $element->getAttribute('width');
                $height = $element->getAttribute('height');
                if (!$width || !$height) {
                    $attrs = explode(";", $element->getAttribute('style'));
                    $style = "";
                    foreach ($attrs as $attr) {
                        if (strlen(trim($attr)) > 0) {
                            $kv = explode(":", trim($attr));
                            if (trim($kv[0]) == "width") {
                                $width = (int)($kv[1]);
                            } elseif (trim($kv[0]) == "height") {
                                $height = (int)($kv[1]);
                            } else {
                                $style .= trim($kv[0]) . ":" . trim($kv[1]) . ";";
                            }
                        }
                    }
                    $element->setAttribute('style', $style);
                }

                if ($width && $height) {
                    $width = (int)$width;
                    $height = (int)$height;
                    $ratio = $height / $width * 100;
                    $element->removeAttribute('width');
                    $element->removeAttribute('height');
                    $wrapper_clone = $wrapper->cloneNode();
                    $wrapper_clone->setAttribute('style', "padding-top: $ratio%");
                    $element->parentNode->replaceChild($wrapper_clone, $element);
                    $wrapper_clone->appendChild($element);
                } elseif ($element->tagName == 'iframe') {
                    $element->setAttribute('allowfullscreen', '');
                    $element->setAttribute('frameborder', '0');

                    $ratio = 75;
                    $wrapper_clone = $wrapper->cloneNode();
                    $wrapper_clone->setAttribute('style', "padding-top: $ratio%");
                    $element->parentNode->replaceChild($wrapper_clone, $element);
                    $wrapper_clone->appendChild($element);
                }
            }

            return html_entity_decode($dom->saveHTML());
       });
    }

    public function comments() {
        return $this->hasMany('App\Models\Comment', 'material_id', 'original_id')->where(['material_type' => $this->type_id]);
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

    public function getFullUrlAttribute() {
        return route('articles.show', $this->attributes['url'] ? $this->attributes['url'] : $this->id);

        $day = $this->day;
        $month = $this->month;
        $year = $this->year;

        if ($this->type_id == MaterialTypes::TYPE_NEWS) {
            $path = "/news/".$year."-".$month."-".$day."-".$this->original_id;
            return $path;
        }
        if ($this->type_id == MaterialTypes::TYPE_ARTICLES) {
            $path = "/blog/".$year."-".$month."-".$day."-".$this->original_id;
            return $path;
        }
        if ($this->type_id == MaterialTypes::TYPE_BLOG) {
            $path = "/stuff/".$this->category_id."-1-0-".$this->original_id;
            return $path;
        }
    }

    public function category() {
        return $this->belongsTo(ArticleCategory::class,'category_id', 'original_id')->where(['type_id' => $this->type_id]);
    }

    public function getSlugAttribute() {
        return $this->attributes['url'];
    }

    public function setSlugAttribute($url) {
        $this->url = $url;
    }

    public function getIsApprovedAttribute()
    {
        return !$this->pending && $this->created_at_original <= time();
    }

    public function user() {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }

    public function getCreatedAtAttribute() {
        if (!isset($this->attributes['created_at'])) {
            return "";
        }
        return DatesHelper::format($this->attributes['created_at'], false);
    }

    public function getCreatedAtOriginalAttribute() {
        if (!isset($this->attributes['created_at'])) {
            return 0;
        }
        return strtotime($this->attributes['created_at']);
    }

    public function coverPicture() {
        return $this->hasOne('App\Models\Picture', 'id', 'cover_id');
    }

    public function getCoverUrlAttribute() {
        return Cache::remember('article_cover_'.$this->id, CacheTimes::RELATION, function () {
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

            return str_replace('vk.me', 'userapi.com', $url);
        });
    }

    public function getSourceWithLinksAttribute() {
        return RegexHelper::parseLinks($this->source);
    }

    public function crossposts() {
        return $this->hasMany(Crosspost::class);
    }

    public function getCommentsCountAttribute() {
       return Cache::remember("comments_count_articles_".$this->id, CacheTimes::RELATION, function () {
           return count($this->comments);
       });
    }


    public function scopeApproved($query) {
        //if (!PermissionsHelper::allows('nwapprove')) {
            $query->where(function($q) {
                $q->where(function($subquery) {
                    $subquery->where(['pending' => false]);
                    $subquery->whereDate('created_at', '<=', Carbon::now());
                });
                $user = auth()->user();
                if ($user) {
                    $q->orWhere(['user_id' => $user->id]);
                }
            });
        //}
        return $query;
    }

    public function tags() {
        return $this->hasManyThrough(Tag::class, TagMaterial::class, 'material_id', 'id', 'id', 'tag_id');
    }

    public function getTagsListAttribute() {
        return Cache::remember("articles_tags_list_".$this->id, CacheTimes::RELATION, function () {
            return $this->tags;
        });
    }

    public function bindings() {
        return $this->hasMany(ArticleBinding::class);
    }
}
