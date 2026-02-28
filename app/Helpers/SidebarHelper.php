<?php
namespace App\Helpers;

use App\Constants\CacheTimes;
use App\Constants\MaterialTypes;
use App\Models\Article;
use App\Models\Record;
use Illuminate\Support\Facades\Cache;

class SidebarHelper {

    public static function getArticles($count = 5) {
        return Cache::remember('sidebar_articles'."_".$count, CacheTimes::RANDOM, function () use($count) {
            return Article::where(['pending' => false])->orderBy('created_at', 'desc')->limit($count)->get();
        });
    }

    public static function getRecords($is_radio = false, $count = 10) {
        return Cache::remember('sidebar_records_'.($is_radio ? 'radio' : 'video')."_".$count, CacheTimes::RANDOM, function () use ($is_radio, $count) {
            return Record::where(['is_radio' => $is_radio])->inRandomOrder()->limit($count)->get();
        });
    }


}
