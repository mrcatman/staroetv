<?php
namespace App\Helpers;

class ArraysHelper {

    public static function diffAssoc($array, $keys) {
       $copy = $array;
       foreach ($keys as $key) {
           unset($copy[$key]);
       }
       return $copy;
    }

}
