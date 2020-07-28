<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class AdditionalChannel extends Model {

    protected $table = "programs_additional_channels";
    protected $guarded = [];

    public function channel() {
        return $this->belongsTo(Channel::class);
    }

}
