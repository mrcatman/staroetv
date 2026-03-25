<?php

namespace App\Console\Commands;

use App\Models\Record;
use Illuminate\Console\Command;

class FixWrongRecordDates extends Command
{
    protected $signature = 'records:fix-wrong-dates';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Record::where('date', '<', '2010-01-01')->update(['date' => '2020-01-01']);
    }
}
