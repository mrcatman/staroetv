<?php
namespace App\Helpers;
class HighlightHelper {

    public static function highlight($text, $string) {
        $string = strip_tags($string);
        return preg_replace('/(?![^<>]*>)'.preg_quote($string,"/").'/i', '<span class="highlight">$0</span>', $text);

    }
}
