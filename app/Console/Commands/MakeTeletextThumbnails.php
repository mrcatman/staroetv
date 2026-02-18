<?php

namespace App\Console\Commands;

use App\Helpers\TeletextHelper;
use App\Models\Teletext;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MakeTeletextThumbnails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'teletext:thumbnails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update teletext thumbnails';

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
        $teletexts = Teletext::whereNull('cover_id')->get();
        foreach ($teletexts as $teletext) {
            TeletextHelper::takeScreenshot($teletext);
            echo 'Teletext '.$teletext->id.': screenshot ready'.PHP_EOL;
        }
    }
}
