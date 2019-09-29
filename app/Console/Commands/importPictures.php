<?php

namespace App\Console\Commands;

use App\Picture;
use App\Program;
use App\Video;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class importPictures extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pictures:import';

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

    /**
     * Execute the console command.
     *
     * @return mixed
     */

    protected function download($url) {
        try {
            $full_url = $url;
            if ($url[0] == "/" || Str::startsWith($url, "http://staroetv.su/") || Str::startsWith($url, "http://staroetv.ucoz.ru/")) {
                $url = str_replace("http://staroetv.su","", $url);
                $url = str_replace("http://staroetv.ucoz.ru","", $url);
                echo "Not downloading because of relative path " . $url . PHP_EOL;
                $picture = new Picture([
                    'url' => $url
                ]);
                $picture->save();
            } else {
                $path = parse_url($full_url, PHP_URL_PATH);
                $basename = pathinfo($path, PATHINFO_BASENAME);
                $name = "/pictures/imported/" . $basename;
                echo "Downloading " . $full_url . PHP_EOL;
                file_put_contents(public_path($name), fopen($full_url, 'r'));
                $picture = new Picture([
                    'url' => $name
                ]);
            }
            $picture->save();
            return $picture->id;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function handle()
    {
        $video_covers = Video::pluck('cover')->unique()->values();
        foreach ($video_covers as $video_cover) {
            $id = $this->download($video_cover);
            Video::where(['cover' => $video_cover])->update([
                'cover_id' => $id
            ]);
        }
        $program_covers = Program::pluck('cover')->unique()->values();
        foreach ($program_covers as $program_cover) {
            $id = $this->download($program_cover);
            Program::where(['cover' => $program_cover])->update([
                'cover_id' => $id
            ]);
        }
    }
}
