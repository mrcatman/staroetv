<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ChannelName extends Model {

    public $table = "channels_names";
    protected $guarded = [];
    protected $with = ['logo'];
    protected $appends = ['years_range'];

    protected $casts = [
        'date_start' => 'date',
        'date_end' => 'date',
        'alternatives' => 'array'
    ];

    public function channel() {
        return $this->belongsTo(Channel::class);
    }

    public function logo() {
        return $this->hasOne(Picture::class, 'id', 'logo_id');
    }

    public function getYearsRangeAttribute() {
        $date_start = $this->date_start;
        $date_end = $this->date_end;
        if (!$date_start && !$date_end) {
            return "";
        }
        if ($date_start && !$date_end) {
            return "с ".$date_start->year;
        }
        if (!$date_start && $date_end) {
            return $date_end->year;
        }
        if ($date_start->year == $date_end->year) {
            return $date_start->year;
        }
        return $date_start->year. " - ". $date_end->year;
    }
}
