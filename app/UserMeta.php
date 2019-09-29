<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class UserMeta extends Model {

    public $table = "users_meta";
    protected $guarded = [];
    public $timestamps = false;

}
