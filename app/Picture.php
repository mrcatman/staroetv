<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class Picture extends Model {

    protected $guarded = [];

    public function loadFromURL($url, $filename = null) {
        $path = parse_url($url, PHP_URL_PATH);
        $basename = pathinfo($path, PATHINFO_BASENAME);
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        if ($filename) {
            $name = "/pictures/imported/" . $filename.".".$extension;
        } else {
            $name = "/pictures/imported/" . $basename;
        }

        file_put_contents(public_path($name), fopen($url, 'r'));
        $this->url = $name;
    }
}
