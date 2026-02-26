<?php

namespace App\Console\Commands;

use App\Models\Picture;
use Illuminate\Console\Command;

class DownloadPictures extends Command
{

    protected $signature = 'pictures:download {domain}';

    protected $description = '';

    public function handle()
    {
        $pictures = Picture::where('url', 'LIKE', '%' . $this->argument('domain') . '%')->get();
        echo 'Found '.count($pictures).' pictures'.PHP_EOL;

        foreach ($pictures as $picture) {
            $old_url = $picture->url;
            $old_url = explode('?', $old_url)[0];
            $picture->loadFromURL($old_url,  null, false, 'imported/'.date("dmY"));
            $picture->save();
            usleep(100000);

            echo 'Downloaded '.$picture->url.' from '.$old_url.PHP_EOL;
        }
    }

}
