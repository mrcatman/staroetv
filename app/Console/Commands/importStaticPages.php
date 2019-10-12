<?php

namespace App\Console\Commands;

use App\Article;
use App\Helpers\CSVHelper;
use App\Page;
use App\UserAward;
use App\UserReputation;
use Carbon\Carbon;
use Illuminate\Console\Command;

class importStaticPages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pages:import';

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
        $pages = CSVHelper::transform(public_path("data/site.csv"), [
           'id', '', '', 'can_read', 'title', '', 'content', 'created_at',
        ], false);
        foreach ($pages as $page) {
            unset($page['']);
            $created_at = $page['created_at'];
            unset($page['created_at']);
            $page_obj = new Page($page);
            $page_obj->created_at = Carbon::createFromTimestamp($created_at);
            $page_obj->save();
            echo "Added page: " . $page['title'] . PHP_EOL;
        }
    }
}
