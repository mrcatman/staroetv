<?php

namespace App\Models;
use App\Constants\CacheTimes;
use App\Constants\MaterialTypes;
use App\Helpers\DatesHelper;
use App\Helpers\PermissionsHelper;
use App\Traits\HasChannel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class Teletext extends Model {

    use HasChannel;


    public $table = 'teletext';

    protected $guarded = [];

    protected $casts = [
        'date' => 'date',
        'pages' => 'array',
    ];

    public function getCanEditAttribute() {
        if (PermissionsHelper::allows("teletext")) {
            return true;
        }
        return auth()->user() && $this->author_id == auth()->user()->id && PermissionsHelper::allows("teletextown");
    }

    public function getUrlAttribute()
    {
        return route('teletext.show', $this->id);
    }

    public function comments() {
        return $this->hasMany(Comment::class, 'material_id', 'original_id')->where(['material_type' => MaterialTypes::TYPE_TELETEXT]);
    }

    public function user() {
        return $this->belongsTo(User::class, 'author_id', 'id');
    }

    public function coverPicture() {
        return $this->hasOne(Picture::class, 'id', 'cover_id');
    }

    public function getCoverAttribute() {
        return Cache::remember('teletext_cover_'.$this->id, CacheTimes::RELATION, function () {
            if ($this->coverPicture) {
                return $this->coverPicture->url;
            }

            return "/pictures/unknown.png";
        });
    }

    public function channel() {
        return $this->belongsTo(Channel::class);
    }

    public function setSupposedDate()
    {
        $year = 1950;
        $month = 1;
        $day = 1;

        if ($this->year) {
            $year = $this->year;
        }
        if ($this->month) {
            $month = $this->month;
        }
        if ($this->day) {
            $day = $this->day;
        }
        $date = Carbon::createFromDate($year, $month, $day);

        $this->date = $date;
        $this->save();
    }

    public function getDateFormattedAttribute()
    {
        $date = $this->year;
        if ($this->day != null) {
            $date = $this->date->format('d.m.Y');
        } else if ($this->month != null) {
            $date = DatesHelper::monthNames()[$this->month].' '.$this->year;
        }
        return $date;
    }

    public function getTitleAttribute()
    {
        return $this->channel_name.' ('.$this->date_formatted.')';
    }

    public function getPageContent($page)
    {
        $dir = '/teletext/'.$this->id.'/'.$page.'.html';
        $file_path = Storage::disk('public_data')->path($dir);
        return file_get_contents($file_path);
    }
}
