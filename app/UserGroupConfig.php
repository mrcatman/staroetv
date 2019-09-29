<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class UserGroupConfig extends Model {

    public $timestamps = false;
    public $table = "user_groups_config";
    protected $guarded = [];

}
