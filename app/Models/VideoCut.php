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

    public function getDownloadPathAttribute() {
        $path = $this->attributes['download_path'];
        if (!$path || $path == '') {
            return null;
        }
        if ($path[0] != "/") {
            return "/".$path;
        }
        return $path;
    }
}
