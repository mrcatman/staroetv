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

class fixEndDates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'records:fixenddates';

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
       $records = Record::where('title', 'LIKE', '%,%-20%)%')->orWhere('title', 'LIKE', '%,%-19%)%')->get();
       foreach ($records as $record) {
           preg_match('/(.*), (.*)-(.*)\)(.*)/', $record->title, $output_array);
           if (!is_numeric($output_array[2]) || !is_numeric($output_array[3])) {

              // $record->year = $output_array[2];
              // $record->year_start = $output_array[2];
              // $record->year_end = $output_array[3];
              // $record->save();
               var_dump($record->title, $record->year, $record->year_start, $record->year_end, $output_array[2], $output_array[3]);
               echo "<br>";
           } else {

           }
       }
    }
}
