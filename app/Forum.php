<?php

namespace App;
use App\Helpers\PermissionsHelper;
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

    public function getCanCreateNewTopicAttribute() {
        if ($this->parent_id < 1) {
            return false;
        }
        if (PermissionsHelper::allows('frclosef')) {
            return true;
        }
        if (PermissionsHelper::allows('frthread')) {
            if ($this->can_create_topics == "0") {
                return true;
            }
            $groups = explode(",", $this->can_create_topics);
            return in_array(auth()->user()->group_id, $groups);
        }
        return false;
    }


    public function getIsReadAttribute() {
        if (!auth()->user()) {
            return true;
        }
        $topics = ForumTopic::where(['forum_id' => $this->id])->orderBy('last_reply_at', 'desc')->limit(20)->get()->pluck('last_reply_at_ts', 'id');
        $times = ForumTracking::where(['is_forum' => false, 'user_id' => auth()->user()->id])->whereIn('entity_id', $topics->keys())->pluck('timestamp', 'entity_id');

        if (count($times) === 0) {
            return true;
        }
        $unread = false;
        $start = 1597771000;
        foreach ($times as $topic_id => $time) {
            if ($time < $start) {
                continue;
            }
            if ($unread) {
                continue;
            }
            if($topics->get($topic_id) > $time) {
                $unread = true;
            }
        }

        return !$unread;
    }
}
