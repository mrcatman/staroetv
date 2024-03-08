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

    function startsWithUpper($str) {
        $chr = mb_substr ($str, 0, 1, "UTF-8");
        return mb_strtolower($chr, "UTF-8") != $chr;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
       // $records = Record::where(['is_advertising' => false])->where('title', 'LIKE', 'Реклама %')->where('title', 'NOT LIKE', 'Реклама (%')->where('title', 'NOT LIKE', 'Реклама и %')->get();
        $records = Record::where(function($q) {
            $q->where('title', 'LIKE', 'Реклама%(Кострома%)');
        })->get();
        foreach ($records as $record) {
            preg_match('/Реклама \(Кострома, ~(.*?)\) (.*)/', $record->title, $output_array);
            $record->is_interprogram = false;
            $record->channel_id = null;
            $record->is_advertising = true;
            $brand = trim($output_array[2]);
            if (mb_strpos($brand, "(", 0, "UTF-8") !== false) {
                $brand = mb_substr($brand, 0, mb_strpos($brand, "(", 0, "UTF-8") - 1);
            }

            $record->advertising_brand = $brand;
            $year = explode(" ",$output_array[1])[1];


            $splitted = explode("-", $year);
            if (count($splitted) == 2) {
                if ($year == "1990-ые") {
                    $record->year = null;
                    $record->year_start = null;
                    $record->year_end = null;
                } else {
                    $record->year = $splitted[0];
                    $record->year_start = $splitted[0];
                    $record->year_end = $splitted[1];
                }
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
