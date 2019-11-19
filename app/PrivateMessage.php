<?php

namespace App;
use App\Helpers\DatesHelper;
use Illuminate\Database\Eloquent\Model;

class PrivateMessage extends Model {

    protected $guarded = [];

    public function getCreatedAtAttribute() {
        if (!isset($this->attributes['created_at'])) {
            return "";
        }
        return DatesHelper::format($this->attributes['created_at']);
    }

}
