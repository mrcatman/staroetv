<?php

namespace App;
use App\Helpers\PermissionsHelper;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class InterprogramPackage extends Model {

    protected $guarded = [];

    protected $with = ['coverPicture'];
    protected $appends = ['years_range'];

    const TYPE_INTERPROGRAM = 102;


    public function getNameAttribute() {
        $name = $this->attributes['name'];
        if (!$name || $name == "") {
            //return $this->year_start . "-" . $this->year_end;
        }
        return $name;
    }

    public function coverPicture() {
        return $this->hasOne('App\Picture', 'id', 'cover_id');
    }

    public function getCoverWithRandomAttribute() {
        if ($this->coverPicture) {
            return $this->coverPicture->url;
        } else {
            $record = Record::where(['interprogram_package_id' => $this->id])->whereNotNull('cover_id')->inRandomOrder()->first();
            if ($record) {
                return $record->coverPicture->url;
            }
            return "";
        }
    }

    public function channel() {
        return $this->belongsTo('App\Channel');
    }

    public function pictures() {
        return $this->hasMany('App\InterprogramPackagePicture', 'package_id', 'id');
    }


    public function records() {
        return $this->hasMany('App\Record', 'interprogram_package_id', 'id')->orderBy('internal_order', 'ASC');
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

    public function getCanEditAttribute() {
        if (PermissionsHelper::allows("additional")) {
            return true;
        }
        $user = auth()->user();
        if ($user) {
            return $this->author_id == $user->id && PermissionsHelper::allows("additionalown");
        }
        return false;
    }

    public function visibleRecords() {
        return $this->hasMany('App\Record', 'interprogram_package_id', 'id')->orderBy('internal_order', 'ASC')->where(['is_selected' => true]);
    }

    public function getFullUrlAttribute() {
        $url = $this->url ? $this->url : $this->id;
        $channel = $this->channel;
        return "/".($channel->is_radio ? "radio-stations" : "channels") . "/".$channel->url."/".($channel->is_radio ? "jingles" : "graphics") . "/" . $url;
    }

    public function getFullNameAttribute() {
        return $this->name != "" ? ($this->name . ($this->years_range != "" ? " (".$this->years_range.")" : "")) : $this->years_range;
    }
}
