<?php

namespace App\Console\Commands;

use App\Helpers\CSVHelper;
use App\UserAward;
use App\UserReputation;
use Carbon\Carbon;
use Illuminate\Console\Command;

class importAwards extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'awards:import';

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
        $awards = CSVHelper::transform(public_path("data/awards.csv"), [
            'id', 'award_id', 'to_id', 'from_id', 'created_at', 'comment',
        ], true);
        foreach ($awards as $awards_item) {
            $created_at = $awards_item['created_at'];
            unset($awards_item['created_at']);
            $awards_obj = new UserAward($awards_item);
            $awards_obj->created_at = Carbon::createFromTimestamp($created_at);
            $awards_obj->save();
            echo "Added award for user ".$awards_item['to_id']." from ".$awards_item['from_id'].PHP_EOL;
        }
    }
}
