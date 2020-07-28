<?php

namespace App\Console\Commands;

use App\Article;
use App\Channel;
use App\Helpers\CSVHelper;
use App\Record;
use App\UserAward;
use App\UserReputation;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SetRecordIds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'records:ids';

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
        $records = Record::whereNull('ucoz_id')->get();
        $max = Record::max('ucoz_id') + 1;
        foreach ($records as $record) {
            $record->ucoz_id = $max;
            $record->save();
            $max++;
        }
        var_dump(count($records));
    }
}
