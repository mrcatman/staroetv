<?php

namespace App\Console\Commands;

use App\Models\Record;
use Illuminate\Console\Command;

class UpdateRecordsSupposedDates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'records:update-supposed-dates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Record::chunk(100, function($records) {
            foreach ($records as $record) {
                $record->setSupposedDate();
                echo "Record: ".$record->title." Date: ".$record->supposed_date." Date end: ".$record->supposed_date_end.PHP_EOL;
            }
        });
    }
}
