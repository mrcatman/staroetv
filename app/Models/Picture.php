<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

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

        if (str_starts_with($url, '/') && app()->isLocal()) {
             //return 'https://staroetv.su'.$url;
        }
        return $url;
    }

    public function loadFromURL($url, $filename = null, $find_extension = false, $folder = "imported") {
        if (str_contains($url, 'https://i.ytimg.com/vi/')) { // proxy from YT
            $url = str_replace('https://i.ytimg.com/vi/', 'https://media.staroetv.su/ytimg/', $url);
            $filename_data = explode('/', $url);
            if (count($filename_data) > 5) {
                $filename = $filename_data[4] . '-' . pathinfo($filename_data[5], PATHINFO_FILENAME);
            }
        }

        $domain = parse_url($url, PHP_URL_HOST);
        if ($domain) {
            $path = parse_url($url, PHP_URL_PATH);
            $basename = pathinfo($path, PATHINFO_BASENAME);
            if ($find_extension) {
                $extension = mb_strpos($url, "jpg", 0, "UTF-8") === false ? (Str::endsWith($url, "svg") ? "svg" : "png") : "jpg";
            } else {
                $extension = pathinfo($path, PATHINFO_EXTENSION);
            }
            if (!$extension) {
                $extension = "png";
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
            if (file_exists(public_path($name))) {
                $name = "/pictures/$folder/" . ($filename ?? $basename) . '-'.sha1($url). "." . $extension;
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

    public function compress(): string | null{
        if (str_starts_with($this->url, 'http') || str_ends_with($this->url, '.svg')) {
            return null;
        }
        $storage = Storage::disk('public_data');

        $image = Image::read($storage->path($this->url));
        if ($image->width() > 900) {
            $image->scale(900);
        }
        $encoded = $image->toWebp(quality: 90);

        $pathinfo = pathinfo($this->url);
        $new_url = $pathinfo['dirname'].'/'.$pathinfo['filename'].'.webp';

        $encoded->save($storage->path($new_url));
        $storage->delete($this->url);

        $this->url = $new_url;
        return $new_url;
    }
}
