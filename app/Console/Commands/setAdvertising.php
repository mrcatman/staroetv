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

class setAdvertising extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'records:advertising';

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
        $records = Record::where('title', 'LIKE', 'Реклама (19%')->orWhere('title', 'LIKE', 'Реклама (20%')->get();
        foreach ($records as $record) {
            preg_match('/Реклама \((.*?)\)(.*)/', $record->title, $output_array);
            $record->is_interprogram = false;
            $record->channel_id = null;
            $record->is_advertising = true;
            $record->advertising_brand = trim($output_array[2]);
            $year = $output_array[1];
            $splitted = explode("-", $year);
            if (count($splitted) == 2) {
                $record->year = $splitted[0];
                $record->year_start = $splitted[0];
                $record->year_end = $splitted[1];
            } else {
                if (strpos($year, "?") !== false) {
                    $record->year = null;
                } else {
                    $record->year = $year;
                }
            }
            $record->save();
            $record->setSupposedDate();
        }
    }
}
