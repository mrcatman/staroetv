<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Smile extends Model {

    protected $guarded = [];

    public function picture() {
        return $this->hasOne('App\Models\Picture', 'id', 'picture_id');
    }

}
