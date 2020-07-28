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

class restoreChannels extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'channels:restore';

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
        $videos = Record::where(['is_advertising' => true])->where('title', 'not like', '%еклам%')->get();

        $unique = $videos->unique('channel_id');
        foreach ($unique as $video) {
            $channel = Channel::firstOrCreate([
                'name' => $video->title." / temp"
            ]);
            $channel->id = $video->channel_id;
            $channel->save();
        }
        Record::whereIn('id', $videos->pluck('id'))->update(['is_advertising' => 0]);
    }
}
