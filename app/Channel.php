<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class Channel extends Model {

    protected $guarded = [];

    public function videos() {
        return $this->hasMany('App\Video');
    }

    public function programs() {
        return $this->hasMany('App\Program');
    }

    public function names() {
        return $this->hasMany('App\ChannelName');
    }

    public function interprogramPackages() {
        return $this->hasMany('App\InterprogramPackage');
    }


    public function getNameAttribute() {
        return str_replace("&quot;", "", $this->attributes['name']);
    }

    public function logo() {
        return $this->hasOne('App\Picture', 'id', 'logo_id');
    }


}
