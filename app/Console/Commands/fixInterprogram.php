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

class fixInterprogram extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'records:fixinterprogram';

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
        $records = Record::where(['is_interprogram' => true])->whereNotNull('program_id')->get();

        foreach ($records as $record) {
           if (mb_strpos($record->title, "ставка программы", 0, "UTF-8") === false) {
               if (!in_array($record->id, [10763, 11816, 11444, 15018])) {
                   $record->program_id = null;
                   $record->save();
               }
           }
        }
    }
}
