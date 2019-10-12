<?php
namespace App\Helpers;
class HighlightHelper {

    public static function highlight($text, $string) {
        $highlighted = preg_replace("~$string~iu", "<span class='highlight'>$0</span>", $text);
        return $highlighted;
    }
}