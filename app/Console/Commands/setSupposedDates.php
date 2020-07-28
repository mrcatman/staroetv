<?php

namespace App\Console\Commands;

use App\Article;
use App\Channel;
use App\Helpers\CSVHelper;
use App\Program;
use App\UserAward;
use App\UserReputation;
use App\Record;
use Carbon\Carbon;
use Illuminate\Console\Command;

class setSupposedDates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'records:dates';

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
        $records = Record::all();
        foreach ($records as $record) {
            $year = 1950;
            $month = 1;
            $day = 1;
            if ($record->date) {
                $date = $record->date;
            } else {
                if ($record->year) {
                    $year = $record->year;
                } else {
                    if ($record->year_start) {
                        $year = $record->year_start;
                    }
                }
                if ($record->month) {
                    $month = $record->month;
                }
                if ($record->day) {
                    $day = $record->day;
                }
                $date = Carbon::createFromDate($year, $month, $day);

            }
            $record->supposed_date = $date;
            $record->save();
            echo "Date: ".$record->supposed_date.PHP_EOL;
        }
    }
}
