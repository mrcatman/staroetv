<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Award;
use App\Models\Picture;
use Illuminate\Console\Command;

class importAvatars extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'avatars:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $pictures = Picture::where('url', 'LIKE', '/avatar%')->get();
        foreach ($pictures as $picture) {
            if (!file_exists(public_path($picture->url))) {
                $url = explode("/", $picture->url);
                $url[count($url) - 1] = "";
                $url = implode("/", $url);
                if (!file_exists(public_path($url))) {
                    mkdir(public_path($url), 0777, true);
                }
                try {
                    file_put_contents(public_path($picture->url), fopen("http://staroetv.ucoz.ru" . $picture->url, 'r'));
                } catch (\Exception $e) {
                    echo "Cannot download: ".$picture->url."<br>";
                }
            }
        }
        dd($pictures->pluck('url'));

    }
}
