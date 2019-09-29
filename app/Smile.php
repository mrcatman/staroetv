<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class Smile extends Model {

    protected $guarded = [];

    public function picture() {
        return $this->hasOne('App\Picture', 'id', 'picture_id');
    }

}
