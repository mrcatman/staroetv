<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class Program extends Model {

    protected $guarded = [];

    public function records() {
        return $this->hasMany('App\Record');
    }

    public function coverPicture() {
        return $this->hasOne('App\Picture', 'id', 'cover_id');
    }

    public function getNameAttribute() {
        return str_replace("&quot;", "", $this->attributes['name']);
    }

}
