<?php

namespace App\Models;
use App\Helpers\DatesHelper;
use App\Helpers\PermissionsHelper;
use Illuminate\Database\Eloquent\Model;

class ForumMessage extends Model {

    protected $guarded = [];


    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function topic() {
        return $this->belongsTo(ForumTopic::class, 'topic_id', 'id');
    }


    public function getCreatedAtAttribute() {
        if (!isset($this->attributes['created_at'])) {
            return "";
        }
        return DatesHelper::format($this->attributes['created_at']);
    }


    public function getCreatedAtTsAttribute() {
        if (!isset($this->attributes['created_at'])) {
            return 0;
        }
        return strtotime($this->attributes['created_at']);
    }

    public function getEditedAtAttribute() {
        return DatesHelper::format($this->attributes['edited_at']);
    }

    public function getContentAttribute() {
        $text = $this->attributes['content'];
        if (strpos($text, "_uVideoPlayer") !== false) {
            $text = preg_replace('/<script(.*?)>_uVideoPlayer\({(.*?)},(.*?)\);<\/script>/', '<div class="forum-message__video-player" data-params={$2} data-element=$3></div>', $text);
        }
        $text = str_replace("/.s/img/fr/ic/11/lastpost.gif", "https://staroetv.su/.s/img/fr/ic/11/lastpost.gif", $text);
        return $text;
    }

    public function getOriginalTextAttribute() {
        return $this->attributes['content'];
    }



    public function getCanEditAttribute() {
        if (PermissionsHelper::allows("fredtmsall")) {
            return true;
        }
        $user = auth()->user();
        if ($user) {
            return $this->user_id == $user->id && PermissionsHelper::allows("fredtmsown");
        }
        return false;
    }

    public function getCanDeleteAttribute() {
        if (PermissionsHelper::allows("frdelmsall")) {
            return true;
        }
        $user = auth()->user();
        if ($user) {
            return $this->user_id == $user->id && PermissionsHelper::allows("frdelmsown");
        }
        return false;
    }

}
