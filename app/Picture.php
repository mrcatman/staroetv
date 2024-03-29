<?php

namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Picture extends Model {

    protected $guarded = [];

    public function getUrlAttribute() {
        $url = $this->attributes['url'];
        if (strpos($url, "staroetv.su") !== false) {
            $url = str_replace("http://staroetv.su", "", $url);
        }
        if (strpos($url, "http://staroetv.ucoz.ru") !== false) {
            $url = str_replace("http://staroetv.ucoz.ru", "", $url);
        }
        return $url;
    }

    public function loadFromURL($url, $filename = null, $find_extension = false, $folder = "imported") {
        $domain = parse_url($url, PHP_URL_HOST);
        if ($domain) {
            $path = parse_url($url, PHP_URL_PATH);
            $basename = pathinfo($path, PATHINFO_BASENAME);
            if ($find_extension) {
                $extension = mb_strpos($url, "jpg", 0, "UTF-8") === false ? (Str::endsWith($url, "svg") ? "svg" : "png") : "jpg";
            } else {
                $extension = pathinfo($path, PATHINFO_EXTENSION);
            }
            if ($extension === "svg" && strpos($url, "scale-to-width-down") !== false) {
                $extension = "png";
            }

            if ($filename) {
                $name = "/pictures/$folder/" . $filename . "." . $extension;
            } else {
                $name = "/pictures/$folder/" . $basename;
            }
            if (!file_exists(public_path("/pictures/$folder/"))) {
                mkdir(public_path("/pictures/$folder/"), 0777, true);
            }
            try {
                file_put_contents(public_path($name), fopen($url, 'r'));
                $this->url = $name;
            } catch (\Exception $e) {
                $this->url = $url;
            }
        } else {
            $this->url = $url;
        }

    }
}
