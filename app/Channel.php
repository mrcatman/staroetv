<?php

namespace App;
use App\Helpers\PermissionsHelper;
use Illuminate\Database\Eloquent\Model;

class Channel extends Model {

    protected $guarded = [];
    protected $appends = ['full_url'];

    const TYPE_CHANNELS = 100;

    public function comments() {
        return $this->hasMany('App\Comment', 'material_id', 'original_id')->where(['material_type' => self::TYPE_CHANNELS]);
    }

    public function records() {
        return $this->hasMany('App\Record');
    }

    public function interprogramRecords() {
        return $this->hasMany('App\Record')->where(['is_interprogram' => true]);
    }


    public function programs() {
        return $this->hasMany('App\Program')->orderBy('order');
    }

    public function names() {
        return $this->hasMany('App\ChannelName')->orderBy('date_start');
    }

    public function interprogramPackages() {
        return $this->hasMany('App\InterprogramPackage')->orderBy('date_start');
    }


    public function getNameAttribute() {
        return str_replace("&quot;", "", $this->attributes['name']);
    }

    public function logo() {
        return $this->hasOne('App\Picture', 'id', 'logo_id');
    }

    public function getCanEditAttribute() {
        if (PermissionsHelper::allows("channels")) {
            return true;
        }
        $user = auth()->user();
        if ($user) {
            return $this->author_id == $user->id && PermissionsHelper::allows("channelsown");
        }
        return false;
    }

    public function getBaseUrlAttribute() {
        if ($this->is_radio) {
            return "/radio-stations/";
        } else {
            return "/channels/";
        }
    }

    public function getFullUrlAttribute() {
        $url = $this->url;
        if (!$url) {
            $url = $this->id;
        }
        return $this->base_url.$url;
    }

    public static function findByIdOrUrl($id) {
        $channel = Channel::find($id);
        if (!$channel) {
            $channel = Channel::where(['url' => $id])->first();
        }
        return $channel;
    }

    public function getIsRadioAttribute() {
        return (bool)$this->attributes['is_radio'];
    }

    public function additionalPrograms() {
        return $this->hasManyThrough(
            'App\Program',
            'App\AdditionalChannel',
            'channel_id',
            'id',
            'id',
            'program_id'
        );
    }

    public function getNamesWithLogosAttribute() {
        return $this->names->filter(function($name) {
            return !!$name->logo;
        });
    }

    public function getUniqueNamesAttribute() {
        $names = $this->names->pluck('name')->filter(function($name) {
            return $name != "" && $name != $this->name;
        })->unique();
        return $names;
    }

    public function getUniqueNamesListAttribute() {
        return $this->unique_names->join(", ");
    }

    public function getAllNamesWithMainAttribute() {

        $unique_names = $this->names->pluck('name')->filter(function($name) {
            return $name != "";
        })->unique()->values();
        if (count($unique_names) === 0) {
            return $this->name;
        }
        $main_name = $this->name;
        if ($unique_names->contains($main_name)) {
            if (count($unique_names) === 1) {
                return $main_name;
            }
            $unique_names = $unique_names->filter(function ($name) use ($main_name) {
                return $name != $main_name;
            });
        }
        $unique_names->prepend($main_name);
        $first_name = $unique_names->shift();

        $names = $first_name . " (" . implode(", ", $unique_names->toArray()) . ")";
        return $names;
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

    public function getPageRandomVideosCountAttribute() {
        if (request()->has('test')) {
            if (!$this->description) {
                return 1;
            }
            $description_length = mb_strlen($this->description);

            $count =  floor($description_length / 380) - 4;
            return $count > 1 ? ($count > 7 ? 7 : $count) : 1;
        }
        return 10;
    }

    public function articles() {
        return $this->hasManyThrough(
            Article::class,
            ArticleBinding::class,
            'channel_id',
            'id',
            'id',
            'article_id'
        );
    }

}
