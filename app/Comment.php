<?php

namespace App;
use App\Helpers\BBCodesHelper;
use App\Helpers\DatesHelper;
use App\Helpers\PermissionsHelper;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model {

    protected $guarded = [];

    public function user() {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }

    public function getCanEditAttribute() {
        if (PermissionsHelper::allows("comedit")) {
            return true;
        }
        $user = auth()->user();
        if ($user) {
            return $this->user_id == $user->id && PermissionsHelper::allows("comoedit");
        }
        return false;
    }

    public function getCanDeleteAttribute() {
        if (PermissionsHelper::allows("comdel")) {
            return true;
        }
        $user = auth()->user();
        if ($user) {
            return $this->user_id == $user->id && PermissionsHelper::allows("comodel");
        }
        return false;
    }

    public function getOriginalTextAttribute() {
        $original = $this->attributes['original_text'];
        if ($original) {
            return $original;
        }
        return BBCodesHelper::HTMLToBB($this->text);
    }

    public function getCreatedAtAttribute() {
        if (!isset($this->attributes['created_at'])) {
            return "";
        }
        return DatesHelper::format($this->attributes['created_at']);
    }


    public function getTextAttribute() {
        $text = $this->attributes['text'];
        $text = str_replace("<br /><br />", "<br />", $text);
        if (strpos($text, "_uVideoPlayer") !== false) {
             $text = preg_replace('/<script(.*?)>_uVideoPlayer\({(.*?)},(.*?)\);<\/script>/', '<div class="comment__video-player" data-params={$2} data-element=$3></div>', $text);
        }
        return $text;
    }

    public function getUrlAttribute() {
        if ($this->material_type === Article::TYPE_NEWS || $this->material_type === Article::TYPE_ARTICLES || $this->material_type === Article::TYPE_BLOG) {
            $article = Article::where(['type_id' => $this->material_type, 'original_id' => $this->material_id])->first();
            if ($article) {
                return $article->url;
            }
        } elseif ($this->material_type === Record::TYPE_VIDEOS) {
            $record = Record::where(['ucoz_id' => $this->material_id])->first();
            if ($record) {
                return $record->url;
            }
        } elseif ($this->material_type === 4) {

        } else {
          //  dd($this->material_type);
        }
    }

    public function getTotalRatingAttribute() {
        return $this->rating + $this->newRating->sum('weight');
    }

    public function newRating() {
        return $this->hasMany(CommentRating::class);
    }

}
