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
        Record::where('title', 'like', '%еклам%')->update(['is_advertising' => true]);
    }
}
