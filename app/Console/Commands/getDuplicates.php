<?php

namespace App\Console\Commands;

use App\Channel;
use App\Helpers\CSVHelper;
use App\Picture;
use App\Program;
use App\Record;
use App\UserAward;
use App\UserReputation;
use Carbon\Carbon;
use Illuminate\Console\Command;

class getDuplicates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'records:duplicates';

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
        $programs = Program::whereNotNull('url')->get();
        $unique = $programs->unique('url');
        $duplicates = $programs->diff($unique);
        foreach ($duplicates as $duplicate) {
            $programs_duplicates = Program::where(['url' => $duplicate->url])->get();
            dd($programs_duplicates);
        }

        $channels = Channel::whereNotNull('url')->get();
        $unique = $channels->unique('url');
        $duplicates = $channels->diff($unique);
        foreach ($duplicates as $duplicate) {
            $channels_duplicates = Channel::where(['url' => $duplicate->url])->get();
            dd($channels_duplicates);
        }
    }
}
