<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ForumTracking extends Model {

    protected $guarded = [];

    public $table = "forum_tracking";
    public $timestamps = false;
}
