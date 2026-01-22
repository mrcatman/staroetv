<?php

namespace App\Console\Commands;

use App\Models\Record;
use Illuminate\Console\Command;

class SetRecordsRanges extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'records:set-ranges';

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
        Record::where('year_start', '>', 0)->whereNull(['month_start', 'month_end', 'day_start', 'day_end'])->update([
            'month_start' => 1,
            'month_end' => 12,
            'day_start' => 1,
            'day_end' => 31
        ]);
    }
}
