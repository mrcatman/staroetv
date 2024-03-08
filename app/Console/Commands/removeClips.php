<?php

namespace App\Console\Commands;

use App\Helpers\CSVHelper;
use App\Picture;
use App\Program;
use App\Record;
use App\UserAward;
use App\UserReputation;
use Carbon\Carbon;
use Illuminate\Console\Command;

class removeClips extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'records:removeclips';

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
        $picture = Picture::where(['url' => 'https://pp.userapi.com/tw5Rvq039MScjuKfg6VbLsaRnyXIotdTgZN4Gg/MQHlJtPQNpM.jpg'])->first();
        $programs = Program::where(['cover_id' => $picture->id])->get();
        $records = Record::where(['cover_id' => $picture->id])->get();
        foreach ($programs as $program) {
            $program->delete();
        }
        foreach ($records as $record) {
            $record->delete();
        }
        //$records = Record::where

    }
}
