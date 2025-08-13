<?php
namespace App\Helpers;

class RegexHelper
{
    public static function getExternalIdFromEmbedCode($embed_code)
    {
        if (strpos($embed_code, "youtu") !== false) {
            preg_match('/(.*)\/embed\/(.*)\?/', $embed_code, $output);
            if (!isset($output[2])) {
                return null;
            }
            return $output[2];
        } elseif (strpos($embed_code, 'vk.') !== false) {
            preg_match('/(.*)oid=(.*)&id=(.*)&hash(.*)/', $embed_code, $output);
            if (!isset($output[2]) || !isset($output[3])) {
                return null;
            }
            return $output[2] ."_". $output[3];
        }
        return null;
    }
}
