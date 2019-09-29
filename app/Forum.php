<?php

namespace App;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\DatesHelper;
class Forum extends Model {

    protected $guarded = [];

    public function subforums() {
        return $this->hasMany('App\Forum', 'parent_id', 'id');
    }

    public function topics() {
        return $this->hasMany('App\ForumTopic', 'forum_id', 'id');
    }

    public function fixed_topics() {
        return $this->hasMany('App\ForumTopic', 'forum_id', 'id')->orderBy('last_reply_at', 'DESC')->where(['is_fixed' => 1]);
    }

    public function not_fixed_topics() {
        return $this->hasMany('App\ForumTopic', 'forum_id', 'id')->orderBy('last_reply_at', 'DESC')->where(['is_fixed' => 0]);
    }

    public function getTitleAttribute() {
        return html_entity_decode($this->attributes['title']);
    }

    public function getLastTopicNameAttribute() {
        return html_entity_decode($this->attributes['last_topic_name']);
    }

    public function getLastReplyAtAttribute() {
        return DatesHelper::format($this->attributes['last_reply_at']);
    }

    public function getIsClosedAttribute() {
        return $this->state == 2;
    }
}
