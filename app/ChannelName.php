<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class ChannelName extends Model {

    public $table = "channels_names";
    protected $guarded = [];
    protected $with = ['logo'];

    public function logo() {
        return $this->hasOne('App\Picture', 'id', 'logo_id');
    }
}
