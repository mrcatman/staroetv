<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class Award extends Model {

    protected $guarded = [];

    public function picture() {
        return $this->hasOne('App\Picture', 'id', 'picture_id');
    }

    public function userAwards() {
        return $this->hasMany(UserAward::class);
    }

}
