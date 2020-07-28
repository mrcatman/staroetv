<?php

namespace App\Console\Commands;

use App\Article;
use App\Channel;
use App\Helpers\CSVHelper;
use App\UserAward;
use App\UserReputation;
use Carbon\Carbon;
use Illuminate\Console\Command;

class setRegionalChannels extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'channels:regional';

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
        $channels = Channel::where('name', 'LIKE', '%[%')->get();
        foreach ($channels as $channel) {
            $name = explode("[", $channel->name);
            if (count($name) === 2) {
                $channel->name = trim($name[0]);
                $channel->is_regional = true;
                $city = $name[1];
                $city = trim(explode("]", $city)[0]);
                $channel->city = $city;
                $channel->save();
            }
        }
    }
}
