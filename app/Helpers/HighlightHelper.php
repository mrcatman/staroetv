<?php
namespace App\Helpers;
class HighlightHelper {

    public static function highlight($text, $string, $strip_tags = false) {
        if ($strip_tags) {
            $string = strip_tags($string);
        }

        return preg_replace('/(?![^<>]*>)'.preg_quote($string,"/").'/i', '<span class="highlight">$0</span>', $text);

    }
}
