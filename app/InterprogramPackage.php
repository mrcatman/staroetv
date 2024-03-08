<?php

namespace App;
use App\Helpers\PermissionsHelper;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class InterprogramPackage extends Model {

    protected $guarded = [];

    protected $with = ['coverPicture'];
    protected $appends = ['years_range', 'cover'];

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

    public function getCoverAttribute() {
        if ($this->coverPicture) {
            return $this->coverPicture->url;
        } else {
            return null;
        }
    }

    public function getOneCoverAttribute() {
        if ($this->cover) {
            return $this->cover;
        }
        $pictures = $this->random_pictures;
        if (count($pictures) > 0) {
            return $pictures[0];
        }
        return '/img/noise.jpg';
    }

    public function getRandomPicturesAttribute() {
        return Cache::remember('interprogram_random_pictures_'.$this->id, 60 * 30, function () {
            $records = Record::where(['interprogram_package_id' => $this->id])->whereNotNull('cover_id')->where(function ($q) {
                $q->whereNotIn('interprogram_type', [11, 22]);
                $q->orWhereNull('interprogram_type');
            })->inRandomOrder()->limit(12)->get();
            $pictures = [];
            foreach ($records as $record) {
                if (count($pictures) < 4) {
                    if ($record && $record->cover && $record->cover != '/Obloshki/Zastavka.PNG') {
                        $pictures[] = $record->cover;
                    }
                }
            }
            return $pictures;
        });
    }


    public function channel() {
        return $this->belongsTo('App\Channel');
    }

    public function program() {
        return $this->belongsTo('App\Program');
    }

    public function pictures() {
        return $this->hasMany('App\InterprogramPackagePicture', 'package_id', 'id');
    }

    public function records() {
        return $this->hasMany('App\Record', 'interprogram_package_id', 'id')->orderBy('internal_order', 'ASC');
    }

    public function annotations() {
        return $this->hasMany('App\Annotation', 'interprogram_package_id', 'id')->orderBy('order', 'ASC');
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
        if ($this->program) {
            return $this->program->full_url."/graphics#".$this->id;
        }
        $channel = $this->channel;
        return $channel->full_url."/".($channel->is_radio ? "jingles" : "graphics") . "/" . $url;
    }

    public function getFullNameAttribute() {
        return $this->name != "" ? ($this->name . ($this->years_range != "" ? " (".$this->years_range.")" : "")) : $this->years_range;
    }
}
