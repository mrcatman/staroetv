<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class Picture extends Model {

    protected $guarded = [];

    public function loadFromURL($url, $filename = null, $find_extension = false, $folder = "imported") {
        $domain = parse_url($url, PHP_URL_HOST);
        if ($domain) {
            $path = parse_url($url, PHP_URL_PATH);
            $basename = pathinfo($path, PATHINFO_BASENAME);
            if ($find_extension) {
                $extension = mb_strpos($url, "jpg", 0, "UTF-8") === false ? (mb_strpos($url, "svg", 0, "UTF-8") !== false ? "svg" : "png") : "jpg";
            } else {
                $extension = pathinfo($path, PATHINFO_EXTENSION);
            }

            if ($filename) {
                $name = "/pictures/$folder/" . $filename . "." . $extension;
            } else {
                $name = "/pictures/$folder/" . $basename;
            }
            if (!file_exists(public_path("/pictures/$folder/"))) {
                mkdir(public_path("/pictures/$folder/"), 0777, true);
            }
            file_put_contents(public_path($name), fopen($url, 'r'));
            $this->url = $name;
        } else {
            $this->url = $url;
        }

    }
}
