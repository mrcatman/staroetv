<?php

namespace App\Models;
use App\Helpers\MediaHelper;
use Illuminate\Database\Eloquent\Model;

class VideoCut extends Model {

    protected $guarded = [];

    public const STATUS_PENDING = -1;

    public const STATUS_SUCCESS = 1;
    public const STATUS_ERROR = 0;

    protected $casts = [
        'data' => 'array',
    ];

    public function video() {
        return $this->belongsTo(Record::class);
    }

    public function updateMediaParams()
    {
        $path = public_path($this->download_path);

        $this->fps = MediaHelper::getFps($path);
        $this->frames = MediaHelper::getFrames($path);
    }

}
