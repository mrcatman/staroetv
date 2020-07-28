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

class importSmiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'smiles:import';

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
        Smile::where('id', '>', '-1')->delete();
        Picture::where(['tag' => 'smiles'])->delete();
        $smiles = Storage::disk('public_data')->get("data/smiles.json");
        $smiles = json_decode($smiles);
        foreach ($smiles as $smile) {
            $picture = $smile->icon;
            unset($smile->icon);
            $picture = new Picture([
                'url' => $picture,
                'tag' => 'smiles'
            ]);
            $picture->save();
            $smile_obj = new Smile((array)$smile);
            $smile_obj->picture_id = $picture->id;
            $smile_obj->save();
            echo "Added smile: ".$smile_obj->text.PHP_EOL;
        }

    }
}
