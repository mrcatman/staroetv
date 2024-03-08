<?php

namespace App;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class ChannelName extends Model {

    public $table = "channels_names";
    protected $guarded = [];
    protected $with = ['logo'];
    protected $appends = ['years_range'];

    public function channel() {
        return $this->belongsTo(Channel::class);
    }

    public function logo() {
        return $this->hasOne('App\Picture', 'id', 'logo_id');
    }

    public function getYearsRangeAttribute() {
        $date_start = null;
        $date_end = null;
        if ($this->date_start) {
            $date_start = Carbon::createFromFormat("Y-m-d", $this->date_start);
        }
        if ($this->date_end) {
            $date_end = Carbon::createFromFormat("Y-m-d", $this->date_end);
        }
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
