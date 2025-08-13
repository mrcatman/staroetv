<?php

namespace App\Console\Commands;

use App\Channel;
use App\ChannelName;
use App\Helpers\DatesHelper;
use App\Picture;
use App\Program;
use App\Record;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class autoUpload extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'records:auto-upload {ownerId} {offset?} {userId?}';

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
        $owner_id = '-'.$this->argument('ownerId');
        $token = config('tokens.vk');

        $count = 100;
        $offset = $this->argument('offset') ?: 0 ;
        $pages = 50;

        $videos = collect([]);

        $user = User::find($this->argument('userId') ?: 3358);

        for ($i = 0; $i < $pages; $i++) {
            $items = Cache::remember('vk-videos::'.$owner_id.':'.$offset, now()->addMinutes(120), function() use ($owner_id, $count, $offset, $token) {
                $vk_url = "https://api.vk.com/method/video.get?count=$count&offset=$offset&access_token=$token&v=5.101&owner_id=$owner_id&extended=1";
                $data = json_decode(shell_exec(" curl '$vk_url'"));
                usleep(500000);
                return collect($data->response->items);
            });

            echo 'Loading videos... '.$offset.PHP_EOL;
            $videos = $videos->merge($items);
            $offset+= $count;
        }

        $names_map = [
            'ТВ6' => 'ТВ-6',
            '1 канал' => 'ОРТ',
            'Останкино' => 'ОРТ',
            '1 канал Останкино' => 'ОРТ'
        ];

        $programs_map = [
            'О, счастливчик!' => 'Кто хочет стать миллионером?',
        ];

        $files = collect(glob('/storage/temp-upload/*.mp4'))->map(function($file) {
            return pathinfo($file, PATHINFO_FILENAME);
        });
        $files->each(function($file) use ($user, $videos, $names_map, $programs_map) {
            preg_match('/(.*?)\((.*?), (.*?)\)(.*)/', $file, $matches);
            if (count($matches) < 4) {
                return;
            }
            //var_dump($matches);
            $video = $videos->firstWhere('title', $file);

            if ($video) {
                if (isset($names_map[$matches[2]])) {
                    $matches[2] = $names_map[$matches[2]];
                }
                $channel = Channel::where(['name' => $matches[2]])->first();
                if (!$channel) {
                    $name = ChannelName::where(['name' => $matches[2]])->first();
                    if ($name) {
                        $channel = $name->channel;
                    }
                }
                if ($channel) {
                    $date = DatesHelper::guess($matches[3]);

                    $matches[1] = trim($matches[1]);
                    if (isset($programs_map[$matches[1]])) {
                        $matches[1] = $programs_map[$matches[1]];
                    }
                    $program = Program::where(['name' => $matches[1]])->first();
                    if (!$program) {
                        dump($matches[1]);
                    }
                    if ($program) {
                        $record = new Record([
                            'title' => $file,
                            'ucoz_id' => Record::max('ucoz_id') + 1,
                            'is_from_ucoz' => false,
                            'original_added_at' => Carbon::now(),
                            'author_username' => $user->username,
                            'author_id' => $user->id,
                            'description' => '',
                            'short_contents' => '',
                            'views' => 0
                        ]);
                        $record->fill($date);
                        $record->author_id = $user->id;
                        $record->channel_id = $channel->id;
                        $record->program_id = $program->id;
                        $record->description = $video->description;
                        $cover_url = $video->image[count($video->image) - 1]->url;
                        $record->use_own_player = true;
                        $record->source_type = "local";
                        $record->source_path = "/videos/$file.mp4";
                        $cover = new Picture();
                        $cover->loadFromURL($cover_url, md5($cover_url));
                        $cover->save();
                        $record->cover_id = $cover->id;
                        $record->save();
                        $record->setSupposedDate();

                        $command = 'mv "/storage/temp-upload/'.$file.'.mp4" "/storage/videos/'.$file.'.mp4"';
                        //echo $command.PHP_EOL;
                        shell_exec($command);
                        echo $file.PHP_EOL;
                    }
                }
            }
        });
    }
}
