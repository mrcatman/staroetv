<?php

namespace App;
use App\Helpers\DatesHelper;
use Illuminate\Database\Eloquent\Model;

class UserAward extends Model {

    public $table = "users_awards";
    protected $guarded = [];

    public function from() {
        return $this->belongsTo('App\User', 'from_id', 'id');
    }

    public function to() {
        return $this->belongsTo('App\User', 'to_id', 'id');
    }

    public function award() {
        return $this->hasOne('App\Award', 'id', 'award_id');
    }

    public function getCreatedAtAttribute() {
        if (!isset($this->attributes['created_at'])) {
            return "";
        }
        return DatesHelper::format($this->attributes['created_at']);
    }



}
