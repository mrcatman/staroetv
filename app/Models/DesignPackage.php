<?php

namespace App\Models;
use App\Constants\CacheTimes;
use App\Helpers\PermissionsHelper;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class DesignPackage extends Model {

    protected $guarded = [];
    protected $table = 'interprogram_packages';

    protected $with = ['coverPicture'];
    protected $appends = ['years_range', 'cover'];



    public function getNameAttribute() {
        $name = $this->attributes['name'];
        if (!$name || $name == "") {
            //return $this->year_start . "-" . $this->year_end;
        }
        return $name;
    }

    public function coverPicture() {
        return $this->hasOne(Picture::class, 'id', 'cover_id');
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
        if (!$this->id) {
            return Cache::remember('design_package_random_pictures_other_'.$this->channel_id, CacheTimes::RANDOM, function () {
                $records = Record::where(['is_interprogram' => true, 'channel_id' => $this->channel_id])->whereNotNull('cover_id')->where(function ($q) {
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
        return Cache::remember('design_package_random_pictures_'.$this->id, CacheTimes::RANDOM, function () {
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
        return $this->belongsTo(Channel::class);
    }

    public function program() {
        return $this->belongsTo(Program::class);
    }

    public function pictures() {
        return $this->hasMany(DesignPackagePicture::class, 'package_id', 'id');
    }

    public function records() {
        return $this->hasMany(Record::class, 'interprogram_package_id', 'id')->orderBy('internal_order', 'ASC');
    }

    public function visibleRecords() {
        return $this->records()->where(['is_selected' => true]);
    }

    public function annotations() {
        return $this->hasMany(Annotation::class, 'interprogram_package_id', 'id')->orderBy('order', 'ASC');
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



    public function getFullUrlAttribute() {
        if (!$this->id) {
            if ($this->program_id) {
                return Cache::remember('design_package_url_program_'.$this->program_id, CacheTimes::RELATION, function () {
                    return route('design.programs.show', $this->program->url ?? $this->program->id) . '#package_' . $this->id;
                });
            }
            if ($this->channel_id) {
                return Cache::remember('design_package_url_channel_'.$this->channel_id, CacheTimes::RELATION, function () {
                    $url = $this->url ? $this->url : $this->id;
                    return typed_route('design.[CHANNEL].show', $this->channel->is_radio, [$this->channel->url ?? $this->channel->id, $url]);
                });
            }
        }
        return Cache::remember('design_package_url_'.$this->id, CacheTimes::RELATION, function () {
            $url = $this->url ? $this->url : $this->id;
            if ($this->program) {
                return route('design.programs.show', $this->program->url ?? $this->program->id) . '#package_' . $this->id;
            }
            return typed_route('design.[CHANNEL].show', $this->channel->is_radio, [$this->channel->url ?? $this->channel->id, $url]);
        });
    }

    public function getFullNameAttribute() {
        return $this->name != "" ? ($this->name . ($this->years_range != "" ? " (".$this->years_range.")" : "")) : $this->years_range;
    }
}
