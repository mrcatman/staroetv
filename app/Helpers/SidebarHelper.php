<?php
namespace App\Helpers;

use App\Article;
use App\Record;
use Illuminate\Filesystem\Cache;

class SidebarHelper {

    public static function getArticles() {
        //$articles = Cache::remember('sidebar_articles', 120, function () {
            return Article::where(['pending' => false])->orderBy('id', 'desc')->limit(5)->get();
       // });
    }
    public static function getRecords($is_radio = false) {
        //$articles = Cache::remember('sidebar_articles', 120, function () {
        return Record::where(['is_radio' => $is_radio])->inRandomOrder()->limit(10)->get();
        // });
    }


}
