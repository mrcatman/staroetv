<?php

namespace App\Console\Commands;

use App\Helpers\CSVHelper;
use App\Picture;
use App\Program;
use App\Record;
use App\UserAward;
use App\UserReputation;
use Carbon\Carbon;
use Illuminate\Console\Command;

class revertVideos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'records:revert';

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
        $data = file_get_contents(resource_path('videos_to_revert.txt'));
        $data = explode(PHP_EOL, $data);
        foreach ($data as $item) {
            $item = explode(",", $item);
            if (count($item) <= 1) {
                continue;
            }

            $video = Record::where(['ucoz_id' => $item['21']])->first();
            if ($video) {
            $created_at =  trim(str_replace("'", '', $item[2]));
            try {
                $video->created_at = Carbon::createFromFormat("Y-m-d H:i:s", $created_at);
            } catch (\Exception $e) {

            }
            var_dump($video->created_at);
                $video->author_username = trim(str_replace("'", '', $item[3]));
                $video->author_id = trim($item[4]);
                $video->ucoz_id = Record::max('ucoz_id') + 1;
                $video->save();
            }
        }
    }
}
