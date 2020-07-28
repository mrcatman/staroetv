<?php

namespace App\Console\Commands;

use App\Article;
use App\Award;
use App\Helpers\CSVHelper;
use App\Picture;
use App\Smile;
use App\UserAward;
use App\UserReputation;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class importAwardsList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'awardslist:import';

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

    public function handle()
    {
        Award::where('id', '>', '-1')->delete();
        Picture::where(['tag' => 'awards'])->delete();
        $awards = Storage::disk('public_data')->get("data/awards.json");
        $awards = json_decode($awards);
        foreach ($awards as $award) {
            $picture = $award->icon;
            $picture = str_replace("http://staroetv.ucoz.ru", "", $picture);
            $picture = str_replace("http://staroetv.su", "", $picture);
            unset($award->icon);
            $picture = new Picture([
                'url' => $picture,
                'tag' => 'awards'
            ]);
            $picture->save();
            $award_obj = new Award((array)$award);
            $award_obj->groups_can_add = "4";
            $award_obj->picture_id = $picture->id;
            $award_obj->save();
            echo "Added award: ".$award_obj->id.PHP_EOL;
        }

    }
}
