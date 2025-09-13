<?php

namespace App\Models;
use App\Helpers\DatesHelper;
use App\Helpers\PermissionsHelper;
use App\Traits\HasChannel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Teletext extends Model {

    use HasChannel;

    protected $guarded = [];
    public $table = 'teletext';

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
        return "/teletext/" . $this->id;
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

    public function getTitleAttribute()
    {
        $date = $this->year;
        if ($this->day != null) {
            $date = $this->date->format('d.m.Y');
        } else if ($this->month != null) {
            $date = DatesHelper::monthNames()[$this->month].' '.$this->year;
        }

        return $this->channel_name.' ('.$date.')';
    }

    public function getPageContent($page)
    {
        $dir = '/teletext/'.$this->id.'/'.$page.'.html';
        $file_path = Storage::disk('public_data')->path($dir);
        return file_get_contents($file_path);
    }
}
