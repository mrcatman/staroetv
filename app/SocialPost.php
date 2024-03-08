<?php

namespace App;
use App\Crossposting\CrossposterManager;
use App\Helpers\DatesHelper;
use Illuminate\Database\Eloquent\Model;

class SocialPost extends Model {

    public $table = "crossposting";
    protected $guarded = [];

    protected $appends = ['post_time', 'post_time_iso'];
    protected $with = ['postConnections'];


    protected $casts = [
        'post_data' => 'array',
        'post_data_old' => 'array'
    ];

    public function getCoverAttribute() {
        $data = $this->post_data;
        $picture = null;
        if (isset($data['media'])) {
            foreach ($data['media'] as $media) {
                if ($media['type'] == 'picture' && !$picture) {
                    $picture = $media['value'];
                }
            }
        }
        return $picture;
    }

    public function getTitleAttribute() {
        $data = $this->post_data;
        $title = "";
        if (isset($data['text'])) {
            $title = $data['text'];
        }
        return $title;
    }


    public function getPostTimeAttribute() {
        if (!$this->post_ts) {
            return null;
        }
        return DatesHelper::formatTS($this->post_ts);
    }

    public function getPostTimeIsoAttribute() {
        if (!$this->post_ts) {
            return null;
        }
        return date("c", $this->post_ts);
    }

    public function postConnections() {
        return $this->hasMany(SocialPostConnection::class, 'crosspost_id', 'id');
    }


}
