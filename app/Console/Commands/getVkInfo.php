<?php

namespace App\Console\Commands;

use App\Article;
use App\Channel;
use App\Helpers\CSVHelper;
use App\UserAward;
use App\UserReputation;
use App\Record;
use Carbon\Carbon;
use Illuminate\Console\Command;

class getVkInfo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'videos:covers';

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
    public function handle()
    {
        $videos = Record::where('embed_code', 'like', '%vk.com%')->whereNull('original_cover')->get(); //
      //  $videos = [$videos[0]];
        $token = config('tokens.vk');
        foreach ($videos as $video) {
            preg_match('/(.*?)video_ext.php\?oid=(.*?)&id=(.*?)&hash=(.*?)[&"](.*?)/', $video->embed_code, $matches);
            $user_id = $matches[2];
            $video_id = $matches[3];
            $hash = $matches[4];
            $full_id = $user_id."_".$video_id."_".$hash;
            $data = json_decode(file_get_contents("https://api.vk.com/method/video.get?access_token=$token&v=5.101&videos=$full_id&extended=1"));
            if (isset($data->response) && isset($data->response->items[0])) {
                $video_object = $data->response->items[0];
                if (count($video_object->image) > 0) {
                    if (isset($video_object->image[5])) {
                        $picture = $video_object->image[5]->url;
                    } else {
                        $picture = $video_object->image[2]->url;
                    }
                    echo "Video: " . $video->title . ", picture: " . $picture . PHP_EOL;
                    $video->original_cover = $picture;
                }
                $video->length = $video_object->duration;
                $video->save();
            } else {
                var_dump($data);
                if (isset($data->response) && $data->response->count === 0) {
                    echo "Video deleted ".$video->title.PHP_EOL;
                    $video->delete();

                }
            }
            sleep(1);
        }
    }
}
