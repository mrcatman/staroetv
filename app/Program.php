<?php

namespace App;
use App\Helpers\PermissionsHelper;
use Illuminate\Database\Eloquent\Model;

class Program extends Model {

    protected $guarded = [];

    public function records() {
        return $this->hasMany('App\Record');
    }

    public function coverPicture() {
        return $this->hasOne('App\Picture', 'id', 'cover_id');
    }

    public function channel() {
        return $this->belongsTo('App\Channel');
    }

    public function getNameAttribute() {
        return str_replace("&quot;", "", $this->attributes['name']);
    }

    public function getCanEditAttribute() {
        if (PermissionsHelper::allows("programs")) {
            return true;
        }
        $user = auth()->user();
        if ($user) {
            return $this->author_id == $user->id && PermissionsHelper::allows("programsown");
        }
        return false;
    }


    public function getFullUrlAttribute() {
        $url = $this->url;
        if (!$url) {
            $url = $this->id;
        }
        return "/programs/".$url;
    }
}
