<?php

namespace App;
use App\Helpers\DatesHelper;
use App\Helpers\PermissionsHelper;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Record extends Model {

    protected $guarded = [];
    const TYPE_VIDEOS = 10;
    protected $appends = ['url'];



    public function getTitleAttribute() {
        if (!isset($this->attributes['title'])) {
            return "";
        }
        $title = str_replace("&quot;", '"', $this->attributes['title']);
        $title = str_replace("&#39;", "'", $title);
        return $title;
    }

    public function getTitleWithoutTagsAttribute() {
        return strip_tags(str_replace("<br>", " ", $this->title));
    }

    public function getEmbedCodeAttribute() {
        return str_replace("&autoplay=1", "", $this->attributes['embed_code']);
    }

    public function user() {
        return $this->belongsTo('App\User', 'author_id', 'id');
    }

    public function channel() {
        return $this->belongsTo('App\Channel', 'channel_id', 'id');
    }

    public function program() {
        return $this->belongsTo('App\Program', 'program_id', 'id');
    }

    public function getProgramNameAttribute() {
        if ($this->program) {
            return $this->program->name;
        } else {
            return "?";
        }
    }


    public function getUrlAttribute() {
        if ($this->is_radio) {
            return "/radio/" . $this->id;
        } else {
            return "/video/" . $this->id;
        }
    }


    public function coverPicture() {
        return $this->hasOne('App\Picture', 'id', 'cover_id');
    }

    public function getCoverAttribute() {
        //if (request()->has('test')) {
          //  dd($this->coverPicture, $this);
      //  }
        if ($this->coverPicture) {
            return $this->coverPicture->url;
        }
        if (isset($this->attributes['original_cover']) && $this->attributes['original_cover'] != "") {
            return $this->attributes['original_cover'];
        }
        if (isset($this->attributes['cover']) && $this->attributes['cover'] != "") {
            return $this->attributes['cover'];
        }

        return "/pictures/unknown.png";
    }

    public function generateTitle() {
        if ($this->is_advertising) {
            $text = "Реклама ".$this->advertising_brand;
            if (!$this->year_start || !$this->year_end || $this->year_start == $this->year_end) {
                $text.= " (".$this->year.")";
            } else {
                $text.= " (".$this->year_start."-".$this->year_end.")";
            }
            if ($this->short_description != "") {
                $text.= " ".$this->short_description;
            }
            return $text;
        }
        if ($this->is_interprogram) {
            return $this->generateInterprogramTitle(false);
        }

        if ($this->program_id > 0) {
            $program = $this->program->name;
        } else {
            $program = "Неизвестная программа";
        }
        if ($this->channel_id > 0) {
            $channel = $this->getChannelName();
        } else {
            $channel = "???";
        }
        $date = ($this->day ? str_pad((string)$this->day, 2, " ", STR_PAD_LEFT)."." : "").($this->month ?  str_pad((string)$this->month, 2, " ", STR_PAD_LEFT)."." : "").($this->year ? $this->year : "");
        if ($date == "") {
            $date = "неизвестная дата";
        }
        $short_description = $this->short_description;
        $title = "$program ($channel, $date) $short_description";
        $title = $this->capitalize($title, "UTF-8");
        return $title;
    }

    private function capitalize($string, $encoding)
    {
        $strlen = mb_strlen($string, $encoding);
        $firstChar = mb_substr($string, 0, 1, $encoding);
        $then = mb_substr($string, 1, $strlen - 1, $encoding);
        return mb_strtoupper($firstChar, $encoding) . $then;
    }

    public function getOriginalUrlAttribute() {
        $url = $this->attributes['original_url'];
        if (!$url || $url == "") {
            preg_match('/<iframe(.*?)src="(.*?)"(.*?)/', $this->embed_code, $matches);
            if (isset($matches[2])) {
                return $matches[2];
            }
            preg_match('/<iframe(.*?)src=(.*?) (.*?)/', $this->embed_code, $matches);
            if (isset($matches[2])) {
                return $matches[2];
            }
        }
        return $url;
    }

    public function getCanEditAttribute() {
        if (PermissionsHelper::allows("viedit")) {
            return true;
        }
        $user = auth()->user();
        if ($user) {
            return $this->author_id == $user->id && PermissionsHelper::allows("vioedit");
        }
        return false;
    }

    public function getCanDeleteAttribute() {
        if (PermissionsHelper::allows("videl")) {
            return true;
        }
        $user = auth()->user();
        if ($user) {
            return $this->author_id == $user->id && PermissionsHelper::allows("viodel");
        }
        return false;
    }

    public function getChannelName() {
        $name = null;
        if (!$this->year) {
            $this->year = $this->year_start;
        }
        if ($this->date) {
            $name = ChannelName::where(['channel_id' => $this->channel_id])->whereDate('date_start', '<', $this->date)->whereDate('date_end', '>', $this->date)->first();
        }
        if (!$name && $this->year) {
            $year = $this->year;
            if ($this->interprogramPackage) {
                $year = Carbon::parse($this->interprogramPackage->date_end)->year;
            }
            $year_start = Carbon::createFromDate($year, 1, 1);
            $year_end = Carbon::createFromDate($year, 12, 31);
            $name = ChannelName::where(['channel_id' => $this->channel_id])->whereDate('date_start', '<', $year_end)->whereDate('date_end', '>', $year_start)->first();
            if (!$name) {
                $name = ChannelName::where(['channel_id' => $this->channel_id])->whereDate('date_start', '<', $year_end)->whereNull('date_end')->first();
            }
        }
        if ($name && $name->name != "") {
            //$this->_channel_name_data = $name;
            return $name->name;
        }

        return $this->channel->name;
    }

    public function getChannelLogo() {
        if ($this->_channel_name_data && $this->_channel_name_data->logo) {
            return $this->_channel_name_data->logo->url;
        }
        $name = null;
        if (!$this->year) {
            $this->year = $this->year_start;
        }
        if ($this->date) {
            $name = ChannelName::where(['channel_id' => $this->channel_id])->whereDate('date_start', '<', $this->date)->whereDate('date_end', '>', $this->date)->first();
        }
        if (!$name && $this->year) {
            $year_start = Carbon::createFromDate($this->year, 1, 1);
            $year_end = Carbon::createFromDate($this->year, 12, 31);
            $name = ChannelName::where(['channel_id' => $this->channel_id])->whereDate('date_start', '<', $year_end)->whereDate('date_end', '>', $year_start)->first();
            if (!$name) {
                $name = ChannelName::where(['channel_id' => $this->channel_id])->whereDate('date_start', '<', $year_end)->whereNull('date_end')->first();
            }
        }
        if ($name && $name->logo) {
            return $name->logo->url;
        }
        if ($this->channel && $this->channel->logo) {
            return $this->channel->logo->url;
        }
        return "/pictures/unknown.png";
    }

    public function comments() {
        return $this->hasMany('App\Comment', 'material_id', 'original_id')->where(['material_type' => self::TYPE_VIDEOS]);
    }

    public function getCreatedAtAttribute() {
        if (!isset($this->attributes['created_at'])) {
            return "";
        }
        return DatesHelper::format($this->attributes['created_at']);
    }


    public function getOriginalAddedAtTsAttribute() {
        if (!isset($this->attributes['original_added_at'])) {
            return null;
        }
        return Carbon::parse($this->attributes['original_added_at'])->timestamp;
    }

    public function generateInterprogramTitle($is_short = false) {
        $data = $this->interprogramTypeData;
        if (!$data) {
            return $this->title;
        }
        if ($data->url == "other" && $is_short) {
            return $this->title;
        }
        $text = $data->name;
        $record = $this;

        $channel_and_year_text = "(";
        if (!$is_short) {
            $channel_and_year_text .= $this->getChannelName() . ", ";
        }
        if (!$this->year) {
            $this->year = $this->year_start;
        }
        if (!$this->year_start || !$this->year_end || $this->year_start == $this->year_end) {
            $channel_and_year_text.= $this->year.")";
        } else {
            $channel_and_year_text.= $this->year_start."-".$this->year_end.")";
        }

        if ($data->name_pattern) {
            $text = preg_replace_callback("/[\[{\(].*[\]}\)]/U", function($property) use ($record, $channel_and_year_text, $data) {
                $property = $property[0];
                $property = str_replace("{", "", $property);
                $property = str_replace("}", "", $property);
                if ($property == "data") {
                    return $channel_and_year_text;
                } elseif ($property == "short_description" && $data->url != "program_ident") {
                    $value = $record->short_description;
                    if ($value) {
                        if (strpos($value, '"' === false)) {
                            return '"' . $value . '"';
                        } else {
                            return $value;
                        }
                    }
                } else {
                    $value = $record->{$property};
                    return $value;
                }
            }, $data->name_pattern);
        } else {
            $text.=" ".$channel_and_year_text;
            if ($this->short_description && $this->short_description != "") {
                $text .= "<br>" . $this->short_description;
            }
        }
        $text = trim($text);
        return $text;
    }

    public function getShortTitleAttribute() {
        if ($this->is_advertising) {
            $text =  $this->advertising_brand.($this->year ? " (".$this->year.")" : "");
            if ($this->short_description && $this->short_description != "" && $this->short_description != $this->advertising_brand) {
                $text .= "<br>" . $this->short_description;
            }
            return $text;
        } elseif ($this->is_interprogram && $this->interprogramTypeData) {
            $text = $this->generateInterprogramTitle(true);
            return $text;
        } else {
            return $this->title;
        }
    }

    public function interprogramPackage() {
        return $this->belongsTo(InterprogramPackage::class, "interprogram_package_id", "id");
    }

    public function interprogramTypeData() {
        return $this->belongsTo(Genre::class, "interprogram_type", "id");
    }

    public function advertisingTypeData() {
        return $this->belongsTo(Genre::class, "advertising_type", "id");
    }

    public function setSupposedDate() {
        if (!$this->year && !$this->year_start && !$this->year_end && $this->interprogramPackage) {
            $this->year_start = Carbon::parse($this->interprogramPackage->date_start)->year;
            $this->year_end = Carbon::parse($this->interprogramPackage->date_end)->year;
            $this->save();
        }
        $year = 1950;
        $month = 1;
        $day = 1;
        if ($this->date) {
            $date = $this->date;
        } else {
            if ($this->year) {
                $year = $this->year;
            } else {
                if ($this->year_start) {
                    $year = $this->year_start;
                }
            }
            if ($this->month) {
                $month = $this->month;
            }
            if ($this->day) {
                $day = $this->day;
            }
            $date = Carbon::createFromDate($year, $month, $day);
        }
        $this->supposed_date = $date;
        $this->save();
    }

    public function scopeApproved($query) {
       if (!PermissionsHelper::allows('viapprove')) {
            $query->where(function($q) {
                $q->where(['pending' => false]);
                $user = auth()->user();
                if ($user) {
                    $q->orWhere(['author_id' => $user->id]);
                }
            });
        }
        return $query;
    }

    public function getEmbedYoutubeIdAttribute() {
        preg_match('/embed\/(.*?)"/', $this->embed_code, $output);
        if ($output && count($output) == 2 && strlen($output[1]) == 11) {
            return $output[1];
        }
        return null;
    }

    public function getInterprogramNameAttribute() {
        return Cache::remember('interprogram_name_'.$this->interprogram_type, 3600, function () {
            return $this->interprogramTypeData ? $this->interprogramTypeData->name : "";
        });
    }

    public function getSourceHlsAttribute() {
        return 'https://media.staroetv.su/hls'.$this->source_path.'/index.m3u8';
    }

    public function getDownloadUrlAttribute() {
        return $this->source_path ? 'https://media.staroetv.su'.$this->source_path : null;
    }

    public function getSourceTelegramAttribute() {
        return count($this->all_telegram_sources) > 0  ?$this->all_telegram_sources[0] : null;
    }

    public function getAllTelegramSourcesAttribute() {
        $telegram_id = explode('/', $this->telegram_id);
        if (count($telegram_id) < 2) {
            return [];
        }
        $channel = $telegram_id[0];
        $video_ids = explode(',', $telegram_id[1]);
        $sources = [];
        foreach ($video_ids as $video_id) {
            $sources[] = 'https://staroetv.su/tgvideo/'.$channel.'/'.$video_id.'.mp4';
        }
        return $sources;
    }

    public function getAllTelegramThumbsAttribute() {
        return array_map(function($video) {
            $thumb = str_replace('.mp4', '.jpeg', $video);
            $thumb = str_replace('tgvideo', 'tgpreview', $thumb);
            return $thumb;
        }, $this->all_telegram_sources);
    }
}
