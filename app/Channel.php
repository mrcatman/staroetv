<?php

namespace App;
use App\Helpers\PermissionsHelper;
use Illuminate\Database\Eloquent\Model;

class Channel extends Model {

    protected $guarded = [];
    protected $appends = ['full_url'];

    public function records() {
        return $this->hasMany('App\Record');
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

    public function getCanEditAttribute() {
        if (PermissionsHelper::allows("channels")) {
            return true;
        }
        $user = auth()->user();
        if ($user) {
            return $this->author_id == $user->id && PermissionsHelper::allows("channelsown");
        }
        return false;
    }


    public function getFullUrlAttribute() {
        $url = $this->url;
        if (!$url) {
            $url = $this->id;
        }
        if ($this->is_radio) {
            return "/radio-stations/" .$url;
        } else {
            return "/channels/" . $url;
        }
    }

}
