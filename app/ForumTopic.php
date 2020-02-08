<?php

namespace App;
use App\Helpers\DatesHelper;
use App\Helpers\PermissionsHelper;
use Illuminate\Database\Eloquent\Model;

class ForumTopic extends Model {

    protected $guarded = [];

    public function getLastReplyAtAttribute() {
        return DatesHelper::format($this->attributes['last_reply_at']);
    }

    public function getTitleAttribute() {
        return html_entity_decode($this->attributes['title']);
    }

    public function forum() {
        return $this->belongsTo('App\Forum', 'forum_id', 'id');
    }

    public function getCanEditAttribute() {
        if (PermissionsHelper::allows("fredtthall")) {
            return true;
        }
        $user = auth()->user();
        if ($user) {
            return $this->user_id == $user->id && PermissionsHelper::allows("fredtthown");
        }
        return false;
    }

    public function getCanDeleteAttribute() {
        if (PermissionsHelper::allows("frdelthall")) {
            return true;
        }
        $user = auth()->user();
        if ($user) {
            return $this->user_id == $user->id && PermissionsHelper::allows("frdelthown");
        }
        return false;
    }

    public function questionnaire_data() {
        return $this->hasOne(Questionnaire::class, 'topic_id', 'id');
    }
}
