<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Genre extends Model {

    protected $guarded = [];
    public $timestamps = false;

    public function records()
    {
        return $this->hasMany(Record::class, 'other_category_id', 'id');
    }


}
