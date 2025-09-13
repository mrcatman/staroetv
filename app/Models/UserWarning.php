<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class UserWarning extends Model {

    public $table = "users_warnings";
    protected $guarded = [];

    public function getWeightAttribute() {
        $weight = $this->attributes['weight'];
        return $weight == 1 ? 1 : -1;
    }

    public function from() {
        return $this->belongsTo('App\Models\User', 'from_id', 'id');
    }

    public function to() {
        return $this->belongsTo('App\Models\User', 'to_id', 'id');
    }

    public function getCommentAttribute() {
        return str_replace("&quot;", '"', $this->attributes['comment']);
    }

}
