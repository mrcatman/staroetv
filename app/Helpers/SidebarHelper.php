<?php
namespace App\Helpers;

use App\Article;
use App\Record;
use Illuminate\Support\Facades\Cache;

class SidebarHelper {

    public static function getArticles($count = 5) {
        return Cache::remember('sidebar_articles'."_".$count, 120, function () use($count) {
            return Article::where(['pending' => false])->where('type_id', '!=', Article::TYPE_BLOG)->orderBy('id', 'desc')->limit($count)->get();
        });
    }
    public static function getRecords($is_radio = false, $count = 10) {
        return Cache::remember('sidebar_records_'.($is_radio ? 'radio' : 'video')."_".$count, 120, function () use ($is_radio, $count) {
            return Record::where(['is_radio' => $is_radio])->inRandomOrder()->limit($count)->get();
        });
    }


}
