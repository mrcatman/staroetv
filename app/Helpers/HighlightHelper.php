<?php
namespace App\Helpers;
class HighlightHelper {

    public static function highlight($text, $string, $strip_tags = false) {
        if ($strip_tags) {
            $string = strip_tags($string);
        }
        $highlighted = preg_replace("~$string~iu", "<span class='highlight'>$0</span>", $text);
        return $highlighted;
    }
}