<?php

namespace App;
use App\Helpers\PermissionsHelper;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class InterprogramPackage extends Model {

    protected $guarded = [];

    protected $appends = ['cover_picture', 'years_range'];

    public function getNameAttribute() {
        $name = $this->attributes['name'];
        if (!$name || $name == "") {
            return $this->year_start . "-" . $this->year_end;
        }
        return $name;
    }

    public function getCoverPictureAttribute() {
        $pictures = $this->pictures;
        if (count($pictures) === 0) {
            return "/pictures/unknown.png";
        } else {
            return $pictures[0]->picture->url;
        }
    }

    public function channel() {
        return $this->belongsTo('App\Channel');
    }

    public function pictures() {
        return $this->hasMany('App\InterprogramPackagePicture', 'package_id', 'id');
    }


    public function records() {
        return $this->hasMany('App\Record', 'interprogram_package_id', 'id');
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

}
