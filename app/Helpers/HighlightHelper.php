<?php
namespace App\Helpers;
class HighlightHelper {

    public static function highlight($text, $string, $remove_unneeded_lines = false) {
        $string = strip_tags($string);
        if ($remove_unneeded_lines) {
            $string_lower = mb_strtolower($string);
            $new_text = '';
            $text = explode("\n", $text);
            foreach ($text as $line) {
                if (str_contains(mb_strtolower($line), $string_lower)) {
                    $new_text .= $line . "<br/>";
                }
            }
            $text = $new_text;
        }
        return preg_replace('/(?![^<>]*>)'.preg_quote($string,"/").'/iu', '<span class="highlight">$0</span>', $text);

    }
}
