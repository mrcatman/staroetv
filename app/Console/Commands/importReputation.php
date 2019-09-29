<?php

namespace App\Console\Commands;

use App\Helpers\CSVHelper;
use App\UserReputation;
use Carbon\Carbon;
use Illuminate\Console\Command;

class importReputation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reputation:import';

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
        $reputation = CSVHelper::transform(public_path("data/repute.csv"), [
            'id', 'to_id', 'from_id', 'weight', 'created_at', 'comment', 'link', 'reply_comment'
        ], true);
        foreach ($reputation as $reputation_item) {
            $created_at = $reputation_item['created_at'];
            unset($reputation_item['created_at']);
            $reputation_obj = new UserReputation($reputation_item);
            $reputation_obj->created_at = Carbon::createFromTimestamp($created_at);
            $reputation_obj->save();
            echo "Added reputation for user ".$reputation_item['to_id']." from ".$reputation_item['from_id'].PHP_EOL;
        }
    }
}
