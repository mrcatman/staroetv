<?php
namespace App\Helpers;

class RegexHelper
{

    public static function parseLinks($text)
    {
        $url_pattern = '/\b(?:https?:\/\/|www\.)\S+\b/i';

        return preg_replace_callback($url_pattern, function ($matches) {
            return '<a href="' . htmlspecialchars($matches[0]) . '" target="_blank">' . htmlspecialchars($matches[0]) . '</a>';
        }, $text);
    }

}
