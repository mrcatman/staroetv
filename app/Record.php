<?php

namespace App;
use App\Helpers\PermissionsHelper;
use Illuminate\Database\Eloquent\Model;

class Record extends Model {

    protected $guarded = [];
    const TYPE_VIDEOS = 10;
    protected $appends = ['url'];

    public function getTitleAttribute() {
        if (!isset($this->attributes['title'])) {
            return "";
        }
        return str_replace("&quot;", '"', $this->attributes['title']);
    }

    public function getEmbedCodeAttribute() {
        return str_replace("&autoplay=1", "", $this->attributes['embed_code']);
    }

    public function user() {
        return $this->belongsTo('App\User', 'author_username', 'username');
    }

    public function channel() {
        return $this->belongsTo('App\Channel', 'channel_id', 'id');
    }

    public function program() {
        return $this->belongsTo('App\Program', 'program_id', 'id');
    }


    public function getUrlAttribute() {
        if ($this->is_radio) {
            return "/radio-recordings/" . $this->id;
        } else {
            return "/videos/" . $this->id;
        }
    }


    public function coverPicture() {
        return $this->hasOne('App\Picture', 'id', 'cover_id');
    }

    public function getCoverAttribute() {
        if ($this->attributes['cover'] != "") {
            return $this->attributes['cover'];
        }
        if ($this->coverPicture) {
            return $this->coverPicture->url;
        }
        return "/pictures/unknown.png";
    }

    public function generateTitle() {
        if ($this->program_id > 0) {
            $program = $this->program->name;
        } else {
            $program = "Неизвестная программа";
        }
        if ($this->channel_id > 0) {
            $channel = $this->channel->name;
        } else {
            $channel = "???";
        }
        $date = ($this->day ? $this->day."." : "").($this->month ? $this->month."." : "").($this->year ? $this->year : "");
        if ($date == "") {
            $date = "неизвестная дата";
        }
        $short_description = $this->short_description;
        if ($this->is_interprogram) {
            $title = "$short_description ($channel, $date)";
        } else {
            $title = "$program ($channel, $date) $short_description";
        }
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
        if ($this->date) {
            $name = ChannelName::where(['channel_id' => $this->channel_id])->whereDate('date_start', '<', $this->date)->whereDate('date_end', '>', $this->date)->first();
            if ($name) {
                return $name->name;
            }
        }

        return $this->channel->name;
    }

    public function getChannelLogo() {
        if ($this->date) {
            $name = ChannelName::where(['channel_id' => $this->channel_id])->whereDate('date_start', '<', $this->date)->whereDate('date_end', '>', $this->date)->first();
            if ($name && $name->logo) {
                return $name->logo->url;
            }
            if ($this->channel && $this->channel->logo) {
                return $this->channel->logo->url;
            }
        }
        return "/pictures/unknown.png";
    }

    public function comments() {
        return $this->hasMany('App\Comment', 'material_id', 'original_id')->where(['material_type' => self::TYPE_VIDEOS]);
    }
}
