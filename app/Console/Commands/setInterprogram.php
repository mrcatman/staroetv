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

class setInterprogram extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'videos:interprogram';

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
        $programs = Program::all();
        $interprogram = $programs->filter(function($program) {
            return $program->coverPicture && $program->coverPicture->url == "/Obloshki/Zastavka.PNG";
        });
        Record::whereIn('program_id', $interprogram->pluck('id'))->update([
            'program_id' => null,
            'is_interprogram' => true
        ]);
        foreach ($interprogram as $program) {
            $program->delete();
        }
        $clips = $programs->filter(function($program) {
            return $program->coverPicture && $program->coverPicture->url == "/pictures/imported/eBQ1NOCwagM.jpg";
        });
        Record::whereIn('program_id', $clips->pluck('id'))->update([
            'program_id' => null,
            'is_clip' => true
        ]);
        foreach ($clips as $program) {
            $program->delete();
        }
    }
}
