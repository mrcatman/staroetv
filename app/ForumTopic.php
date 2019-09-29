<?php

namespace App;
use App\Helpers\DatesHelper;
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
}
