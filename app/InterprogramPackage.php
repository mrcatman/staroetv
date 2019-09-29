<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class InterprogramPackage extends Model {

    protected $guarded = [];

    protected $appends = ['cover_picture'];

    public function getNameAttribute() {
        $name = $this->attributes['name'];
        if (!$name || $name == "") {
            return $this->year_start . "-" . $this->year_end;
        }
        return $name;
    }

    public function getCoverPictureAttribute() {
        $pictures = $this->pictures;
        if (count($pictures) === 0) {
            return "/pictures/unknown.png";
        } else {
            return $pictures[0]->picture->url;
        }
    }


    public function pictures() {
        return $this->hasMany('App\InterprogramPackagePicture', 'package_id', 'id');
    }
}
