<?php

namespace App\Models;
use App\Helpers\DatesHelper;
use Illuminate\Database\Eloquent\Model;

class UserAward extends Model {

    public $table = "users_awards";
    protected $guarded = [];

    public function from() {
        return $this->belongsTo('App\Models\User', 'from_id', 'id');
    }

    public function to() {
        return $this->belongsTo('App\Models\User', 'to_id', 'id');
    }

    public function award() {
        return $this->hasOne('App\Models\Award', 'id', 'award_id');
    }

    public function getCreatedAtAttribute() {
        if (!isset($this->attributes['created_at'])) {
            return "";
        }
        return DatesHelper::format($this->attributes['created_at']);
    }



}
