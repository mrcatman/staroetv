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

class setUnknownRecords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'records:unknown';

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
        $channel_ids = Channel::pluck('id');
        //$program_ids = Program::pluck('id');
        //$records = Record::where('program_id', '>', '0')->whereNotIn('program_id', $program_ids)->pluck('title');
        $programs = Program::whereNotIn('channel_id', $channel_ids)->get();
        foreach ($programs as $program) {
            var_dump(count($program->records), $program->name);
        }
    }
}
