<?php

namespace App\Models;
use App\Constants\CacheTimes;
use App\Constants\MaterialTypes;
use App\Helpers\DatesHelper;
use App\Helpers\ExternalServicesHelper;
use App\Helpers\PermissionsHelper;
use App\Helpers\StringsHelper;
use App\Traits\HasChannel;
use Carbon\Carbon;
use cijic\phpMorphy\Facade\Morphy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Record extends Model {

    use HasChannel;

    protected $guarded = [];
    protected $with = ['coverPicture'];
    protected $appends = ['cover', 'url', 'formatted_duration'];

    public function getTitleAttribute() {
        if (!isset($this->attributes['title'])) {
            return "";
        }
        $title = str_replace("&quot;", '"', $this->attributes['title']);
        $title = str_replace("&#39;", "'", $title);
        return $title;
    }

    public function getDescriptionWithTimecodesAttribute()
    {
        return nl2br(preg_replace_callback('/([0-9]{1,2}):([0-9]{1,2})(?::([0-9]{1,2}))?/', function ($timecode) {
            $time = count($timecode) == 4 ? $timecode[1] * 3600 + $timecode[2] * 60 + $timecode[3] : $timecode[1] * 60 + $timecode[2];
            return '<a class="timecode" onclick="player.currentTime = ' . $time . ';player.play()">' . $timecode[0] . '</a>';
        },  $this->description));
    }

    public function getDescriptionTopicsAttribute()
    {
        if (trim($this->description) == '') {
            return [];
        }
        $topics = explode("\n", preg_replace_callback('/([0-9]{1,2}):([0-9]{1,2})(?::([0-9]{1,2}))?/', function ($timecode) {
            return '';
        },  $this->description));
        foreach ($topics as &$topic) {
            $topic = str_replace('- -', '-',$topic);
        }
        return array_slice($topics, 0, 5);
    }

    public function getTitleWithoutTagsAttribute() {
        return strip_tags(str_replace("<br>", " ", $this->title));
    }

    public function getEmbedCodeAttribute() {
        if (!$this->attributes['embed_code']) {
            return "";
        }
        $code =  str_replace("&autoplay=1", "", $this->attributes['embed_code']);
        $iframe_end = '</iframe>';
        $iframe_end_position = strpos($code, $iframe_end);
        return mb_strlen($code) > $iframe_end_position ? mb_substr($code, 0, $iframe_end_position + mb_strlen($iframe_end)) : $code;
    }

    public function user() {
        return $this->belongsTo(User::class, 'author_id', 'id');
    }

    public function channel() {
        return $this->belongsTo(Channel::class, 'channel_id', 'id');
    }

    public function program() {
        return $this->belongsTo(Program::class, 'program_id', 'id');
    }

    public function getProgramNameAttribute() {
        if ($this->program) {
            return $this->program->name;
        } else {
            return "?";
        }
    }

    public function getRoutePrefixAttribute()
    {
        return route_prefix_records($this->is_radio);
    }

    public function getUrlAttribute() {
        return route('records.'.$this->route_prefix.'.show', $this->slug);
    }

    public function getSlugAttribute() {
        return Cache::remember('record_slug_'.$this->id, CacheTimes::RELATION, function () {
            $slug = explode('-', StringsHelper::transliterate($this->title));

            $new_slug = [];
            $length = 0;
            foreach ($slug as $slug_item) {
                if ($length + strlen($slug_item) < 32) {
                    $new_slug[] = $slug_item;
                    $length += strlen($slug_item);
                }
            }
            $new_slug = implode('-', $new_slug);
            return $this->id . '-' . $new_slug;
        });
    }


    public function coverPicture() {
        return $this->hasOne(Picture::class, 'id', 'cover_id');
    }

    public function getCoverAttribute() {
        return Cache::remember('record_cover_'.$this->id, CacheTimes::RELATION, function () {
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
        });
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
            $channel = $this->channel_name;
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

    public function getBroadcastDateAttribute()
    {
        if ($this->day) {
            return str_pad((string)$this->day, 2, " ", STR_PAD_LEFT) . "." . ($this->month ? str_pad((string)$this->month, 2, "0", STR_PAD_LEFT) . "." : "") . ($this->year ? $this->year : "");
        }
        if ($this->month) {
            $month_names = DatesHelper::monthNames();
            if (isset($month_names[$this->month - 1])) {
                return $month_names[$this->month - 1] . ' ' . $this->year;
            }
        }
        return $this->year;
    }

    private function removeExcludes($text)
    {
        $text = preg_replace('"(https?://.*)(?=;)"', '', $text);

        $excludes = ['фрагменты', 'фрагмент', 'отрывок',  'не с начала', 'не до конца'];

        foreach ($excludes as $exclude) {
            $text = trim(str_replace($exclude, '', $text));
        }
        return trim(str_replace('()', '', $text));
    }

    public function getParsedShortDescriptionAttribute()
    {
        if ($this->short_description != '') {
            return $this->removeExcludes($this->short_description);
        }

        preg_match('/(.*?)\((.*?), (.*?)\)(.*)/', $this->title, $matches);
        $description = isset($matches[4]) && $matches[4] != '' ?  trim($matches[4]) : null;
        if (!$description) {
            preg_match('/(.*?)\((.*?)\)(.*)/', $this->title, $matches);
            $description = isset($matches[3]) && $matches[3] != '' ?  trim($matches[3]) : null;
        }
        if (str_starts_with($description, ', ')) {
            $description = substr($description, 2);
        }

        if ($description) {
            return $this->removeExcludes($description);
        }
        return '';
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
        if (isset($record->telegram_id)) {
            $url = $record->all_telegram_sources[0];
        }
        if (!$url || $url == "") {
            preg_match('/<iframe(.*?)src="(.*?)"(.*?)/', $this->embed_code, $matches);
            if (isset($matches[2])) {
                $url = $matches[2];
            }
            if (!$url) {
                preg_match('/<iframe(.*?)src=(.*?) (.*?)/', $this->embed_code, $matches);
                if (isset($matches[2])) {
                   $url = $matches[2];
                }
            }
        }
        $url = str_replace("http://", "https://", $url);
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


    public function comments() {
        return $this->hasMany(Comment::class, 'material_id', 'original_id')->where(['material_type' => MaterialTypes::TYPE_RECORDS]);
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
            $channel_and_year_text .= $this->channel_name . ", ";
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
        return $this->belongsTo(DesignPackage::class, "interprogram_package_id", "id");
    }

    public function interprogramTypeData() {
        return $this->belongsTo(Genre::class, "interprogram_type", "id");
    }

    public function advertisingTypeData() {
        return $this->belongsTo(Genre::class, "advertising_type", "id");
    }

    public function getFormattedDurationAttribute()
    {
        if (!$this->length) {
            return '';
        }
        if ($this->length < 60 * 60) {
            return gmdate("i:s", $this->length);
        }
        return gmdate("H:i:s", $this->length);
    }

    public function setSupposedDate() {
        if (!$this->year && !$this->year_start && !$this->year_end && $this->interprogramPackage) {
            $date_start = Carbon::parse($this->interprogramPackage->date_start);
            $date_end = Carbon::parse($this->interprogramPackage->date_end);

            $this->day_start = $date_start->day;
            $this->month_start = $date_start->month;
            $this->year_start = $date_start->year;

            $this->day_end = $date_end->day;
            $this->month_end = $date_end->month;
            $this->year_end = $date_end->year;
            $this->save();
        }

        if ($this->date) {
            $date = $this->date;
        } else {
            $year = $this->year ?? $this->year_start ?? 1950;
            $month = $this->month ??$this->month_start ?? 1;
            if ($month > 12) {
                $month = 1;
            }

            $day = $this->day ?? $this->day_start ?? 1;
            if ($day > 31) {
                $day = 1;
            }
            $date = Carbon::createFromDate($year, $month, $day);
        }
        $this->supposed_date = $date;

        if ($this->year_end || $this->month_end || $this->day_end) {
            $year = $this->year_end ?? 1950;
            $month = $this->month_end ?? 1;
            $day = $this->day_end ?? 1;
            $date = Carbon::createFromDate($year, $month, $day);
            $this->supposed_date_end = $date;
        }

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
        return ExternalServicesHelper::resolveYoutubeId($this->embed_code);
    }

    public function getInterprogramNameAttribute() {
        return Cache::remember('interprogram_name_'.$this->interprogram_type, CacheTimes::RELATION, function () {
            return $this->interprogramTypeData ? $this->interprogramTypeData->name : "";
        });
    }

    public function getSourceAudioAttribute() {
        return config('site.media_server_url').$this->source_path;
    }

    public function getSourceHlsAttribute() {
        return config('site.media_server_url').'/hls'.$this->source_path.'/index.m3u8';
    }

    public function getDownloadUrlAttribute() {
        return $this->source_path ? config('site.media_server_url').$this->source_path : null;
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

    public function getMultipleEmbedsAttribute() {
        $embeds = explode('|', $this->attributes['embed_code']);
        if (count($embeds) < 2) {
            return null;
        }
        return $embeds;
    }

    public function getSourcesCountAttribute()
    {
        if ($this->telegram_id && count($this->all_telegram_sources) > 1) {
            return count($this->all_telegram_sources);
        }

        if ($this->multiple_embeds) {
            return count($this->multiple_embeds);
        }
        return 1;
    }

    public function scopeSearch($query, $search, $need_sort = true)
    {
        $initial_search = $search;
        $search = preg_replace('/[~,<>;"\'(){}\[\]]/', '', $search);
        $words = collect(explode(' ', $search))
            ->map(function ($term) {
                $normalized = Morphy::getPseudoRoot(mb_strtoupper($term));
                if (!$normalized) {
                    $normalized = [$term];
                }
                return implode(' ', array_map(function ($term) {
                    return "+{$term}*";
                }, array_filter($normalized, function ($term) {return mb_strlen($term) > 2 || is_numeric($term);})));
            })
            ->implode(' ');

        $query->where(function($q) use ($words, $initial_search) {
            $q->where('title', 'like', "%{$initial_search}%");
            $q->orWhereRaw("MATCH(title, short_description, description) AGAINST(? IN BOOLEAN MODE)", [$words]);
            $q->orWhere('short_description', 'like', "%{$initial_search}%");
            $q->orWhere('description', 'like', "%{$initial_search}%");
        });

        if ($need_sort) {
            $query->orderByRaw("
            CASE
                WHEN title LIKE ? THEN 0
                WHEN short_description LIKE ? THEN 1
                WHEN MATCH(title, short_description, description) AGAINST(? IN BOOLEAN MODE) THEN 2
                ELSE 3
            END,
            MATCH(title, short_description, description) AGAINST(? IN BOOLEAN MODE) DESC
        ", [
                "%{$initial_search}%",
                "%{$initial_search}%",
                $words,
                $words,
            ]);
        }

        return $query;
    }

    public function clearCache()
    {
        Cache::forget('record_'.$this->id);
        Cache::forget('record_cover_'.$this->id);
        if ($this->interprogram_package_id) {
            Cache::forget('design_package_records_'.$this->interprogram_package_id);
        }
    }

}
