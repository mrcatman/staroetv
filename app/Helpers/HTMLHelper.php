<?php
namespace App\Helpers;

class HTMLHelper {

    public static function sanitize($text) {
        return strip_tags($text, ['a', 'b', 'br', 'p', 'strong']);
    }


}
