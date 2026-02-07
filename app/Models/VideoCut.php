<?php

namespace App\Models;
use App\Models\Record;
use Illuminate\Database\Eloquent\Model;

class VideoCut extends Model {

    protected $guarded = [];

    protected $casts = [
        'data' => 'array',
    ];

    public function video() {
        return $this->belongsTo(Record::class);
    }

}
