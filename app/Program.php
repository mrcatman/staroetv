<?php

namespace App;
use App\Helpers\PermissionsHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Program extends Model {

    protected $guarded = [];
    protected $appends = ['cover'];

    const TYPE_PROGRAMS = 101;

    public function records() {
        return $this->hasMany('App\Record');
    }

    public function coverPicture() {
        return $this->hasOne('App\Picture', 'id', 'cover_id');
    }

    public function channel() {
        return $this->belongsTo('App\Channel');
    }

    public function getNameAttribute() {
        return str_replace("&quot;", "", $this->attributes['name']);
    }

    public function getCanEditAttribute() {
        if (PermissionsHelper::allows("programs")) {
            return true;
        }
        $user = auth()->user();
        if ($user) {
            return $this->author_id == $user->id && PermissionsHelper::allows("programsown");
        }
        return false;
    }


    public function getFullUrlAttribute() {
        $url = $this->url;
        if (!$url) {
            $url = $this->id;
        }
        return "/programs/".$url;
    }

    public function design() {
        return $this->hasMany('App\Record')->where(['is_interprogram' => true, 'program_id' => $this->id]);
    }

    public function additionalChannels() {
        return $this->hasMany('App\AdditionalChannel');
    }

    public function getRandomPicturesAttribute() {
        return Cache::remember('program_random_pictures_'.$this->id, 60 * 30, function () {
            $records = Record::where(['program_id' => $this->id])->whereNotNull('cover_id')->inRandomOrder()->limit(12)->get();
            $pictures = [];
            foreach ($records as $record) {
                if (count($pictures) < 4) {
                    if ($record && $record->cover && $record->cover != '/Obloshki/11.PNG' && $record->cover != 'http://staroetv.ucoz.ru/Obloshki/11.PNG') {
                        $pictures[] = $record->cover;
                    }
                }
            }
            return $pictures;
        });
    }

    public function getCoverUrlAttribute() {

        if ($this->coverPicture && $this->coverPicture->url != '/Obloshki/11.PNG') {
            return $this->coverPicture->url;
        }
        $pictures = $this->random_pictures;

        if (count($pictures) > 0) {
            return $pictures[0];
        }
       // return '/img/noise.jpg';
        return '/Obloshki/11.PNG';
    }

    public function getChannelsNamesListAttribute() {
        $names = [];
        if ($this->channel) {
            $names[] = $this->channel->name;
        }
        foreach ($this->additionalChannels as $data) {
            if ($data->channel) {
                $names[] = $data->channel->name;
            }
        }
        return implode(", ", $names);
    }

    public function getChannelsHistoryAttribute()
    {
        return Cache::remember('programs_channels___names_' . $this->id, 1800, function () {
            $channels = [];
            if ($this->channel) {
                $date_start = $this->date_of_start;
                $date_end = $this->date_of_closedown;
                if ($date_end && count($this->additionalChannels) > 0) {
                    $date_end = $this->additionalChannels[0]->date_start;
                }
                if (!$date_start) {
                    $record_first = Record::where(['program_id' => $this->id, 'channel_id' => $this->channel_id])->orderBy('supposed_date', 'ASC')->first();
                    if ($record_first) {
                        $date_start = $record_first->supposed_date;
                    };
                }
                if (!$date_end) {
                    $record_last = Record::where(['program_id' => $this->id, 'channel_id' => $this->channel_id])->orderBy('supposed_date', 'DESC')->first();
                    if ($record_last) {
                        $date_end = $record_last->supposed_date;
                    };
                }
                $channels = [
                    [
                        'url' => $this->channel->full_url,
                        'id' => $this->channel->id,
                        'date_start' => $date_start,
                        'date_end' => $date_end
                    ]
                ];
            }
            foreach ($this->additionalChannels as $additional_channel) {
                if ($additional_channel->channel) {
                    $channels[] = [
                        'url' => $additional_channel->channel->full_url,
                        'id' => $additional_channel->channel_id,
                        'date_start' => $additional_channel->date_start,
                        'date_end' => $additional_channel->date_end
                    ];
                }
            }
            $data = [];
            foreach ($channels as $channel) {
                $names = ChannelName::where(['channel_id' => $channel['id']]);
                if ($channel['date_end']) {
                    $names = $names->whereDate('date_end', '>=', $channel['date_end']);
                }
                if ($channel['date_start']) {
                    $names = $names->whereDate('date_start', '<=', $channel['date_start']);
                }
                $names = $names->get();

                if (count($names) === 0) {
                    $names = ChannelName::where(['channel_id' => $channel['id']]);
                    if ( $channel['date_start']) {
                        $names = $names->whereDate('date_start', '<=', $channel['date_start'])->whereNull('date_end');
                    }
                    $names = $names->get();
                }

                if (count($names) === 0) {
                    $channel_data = Channel::find($channel['id']);
                    $name_data = [
                        'url' => $channel_data->full_url,
                        'name' => $channel_data->name,
                        'logo' => $channel_data->logo ? $channel_data->logo->url : null
                    ];
                } else {
                    $name_data = [
                        'url' => $channel['url'],
                        'name' => '',
                        'logo' => null
                    ];
                    $unique_names = $names->pluck('name')->unique()->values();
                    if (count($unique_names) == 1) {
                        $name_data['name'] = $unique_names[0];
                        $names_with_logos = $names->filter(function ($name) {
                            return $name->logo;
                        });
                        if (count($names_with_logos) > 0) {
                            $name_data['logo'] = $names_with_logos[0]->logo->url;
                        }
                    } else {
                        $main_name = $this->channel->name;
                        if ($unique_names->contains($main_name)) {
                            $name_data['logo'] = $this->channel->logo ? $this->channel->logo->url : null;
                            $unique_names = $unique_names->filter(function ($name) use ($main_name) {
                                return $name != $main_name;
                            });
                            $unique_names->prepend($main_name);
                        }
                        if (!$name_data['logo']) {
                            $names_with_logos = $names->filter(function ($name) {
                                return $name->logo;
                            });
                            if (count($names_with_logos) > 0) {
                                $name_data['logo'] = $names_with_logos[0]->logo->url;
                            }
                        }
                        $first_name = $unique_names->shift();
                        $name_data['name'] = $first_name . " (" . implode(", ", $unique_names->toArray()) . ")";
                    }
                }
                if ($name_data['name'] == "") {
                    $channel =  Channel::find($channel['id']);
                    $name_data['name'] = $channel->name;
                }
                $data[] = $name_data;
            }
            return $data;
       });
    }

    public static function findByIdOrUrl($id) {
        $program = Program::find($id);
        if (!$program) {
            $program = Program::where(['url' => $id])->first();
        }
        return $program;
    }

    public function interprogramPackages() {
        return $this->hasMany('App\InterprogramPackage')->orderBy('date_start');
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

    public function getCoverAttribute() {
        if ($this->coverPicture) {
            $url = $this->coverPicture->url;
            if ($url == "/Obloshki/11.PNG") {
                return "/pictures/logo-grey.svg";
            } else {
                return $url;
            }
        } else {
            return "/pictures/logo-grey.svg";
        }
    }

    public function getCoverWithoutEmptyAttribute() {
        if ($this->cover != "/pictures/logo-grey.svg") {

            if ($this->coverPicture && $this->coverPicture->url != '/Obloshki/11.PNG') {
                return $this->coverPicture->url;
            }
            $pictures = $this->random_pictures;

            if (count($pictures) > 0) {
                return $pictures[0];
            }
            return $this->cover;
        } else {
            return null;
        }
    }

    public function getUniqueNamesAttribute() {
        $names = $this->additionalChannels->pluck('title')->filter(function($name)  {
            return $name != "" && $name != $this->name;
        })->unique();
        if ($this->name != $this->original_name) {
            $names->push($this->original_name);
        }
        return $names->toArray();
    }

    public function articles() {
        return $this->hasManyThrough(
            Article::class,
            ArticleBinding::class,
            'program_id',
            'id',
            'id',
            'article_id'
        );
    }


}
