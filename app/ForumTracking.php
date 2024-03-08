<?php

namespace App;
use App\Helpers\DatesHelper;
use App\Helpers\PermissionsHelper;
use Illuminate\Database\Eloquent\Model;

class ForumTracking extends Model {

    protected $guarded = [];

    public $table = "forum_tracking";
    public $timestamps = false;
}
