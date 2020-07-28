<?php
namespace App\Helpers;
class CSVHelper {

    public static function transform($filename, $fields, $original_string = false) {
        $objects = [];
        $file = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($file as $original) {
            $original = str_replace("\|", "", $original);
            $data = str_getcsv($original, "|", PHP_EOL);
            $object = [];
            $i = 0;
            foreach ($fields as $field) {
                if (isset($data[$i])) {
                    $object[$field] = $data[$i];
                }
                $i++;
            }
            if ($original) {
                if ($original_string) {
                    $object['_original'] = $original;
                }
                $objects[] = $object;
            }
        }
        return $objects;
    }

    public static function get($filename) {
        $objects = [];
        if (($handle = fopen($filename, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, "|")) !== FALSE) {
                $objects[] = $data;
            }
            fclose($handle);
        }
        return $objects;
    }
}