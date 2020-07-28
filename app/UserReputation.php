<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class UserReputation extends Model {

    public $table = "users_reputation";
    protected $guarded = [];


    public function from() {
        return $this->belongsTo('App\User', 'from_id', 'id');
    }

    public function to() {
        return $this->belongsTo('App\User', 'to_id', 'id');
    }

    public function getCommentAttribute() {
        return str_replace("&quot;", '"', $this->attributes['comment']);
    }

    public function getLinkAttribute($link) {
        return str_replace("http://staroetv.su", "", $link);
    }


}
