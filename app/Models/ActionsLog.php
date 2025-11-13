<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ActionsLog extends Model {

    protected $guarded = [];

    public function user() {
        return $this->hasOne(User::class);
    }

}
