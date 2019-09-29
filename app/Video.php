<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class Video extends Model {

    protected $guarded = [];

    public function getTitleAttribute() {
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
        return "/videos/".$this->id;
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
        return $title;
    }

    public function getOriginalUrlAttribute() {
        $url = $this->attributes['original_url'];
        if (!$url || $url == "") {
            preg_match('/<iframe(.*?)src="(.*?)"(.*?)/', $this->embed_code, $matches);
            if (isset($matches[2])) {
                return $matches[2];
            }
        }
        return $url;
    }
}
