<?php
namespace App\Helpers;
use App\Comment;

class ViewsHelper {

    public static function increment($material, $type, $column = null) {
        $time_limit = 360;
        $max_count = 10;
        $last_views = request()->session()->get('last_views', []);
        $key = $type."_".$material->id;

        $page_found_in_last_views = false;
        $count = 0;
        krsort($last_views);
        foreach ($last_views as  $time => $last_viewed_page) {
            if (time() - $time > $time_limit || $count >= $max_count) {
                unset($last_views[$last_viewed_page]);
                continue;
            } else {
                if ($last_viewed_page == $key) {
                    $page_found_in_last_views = true;
                }
            }
            $count++;
        }
        if (!$page_found_in_last_views) {
            $last_views[time()] = $key;
            request()->session()->put('last_views', $last_views);
            if (!$column) {
                $column = 'views';
            }
            $material->{$column}++;
            $material->save();
        }

    }

}
