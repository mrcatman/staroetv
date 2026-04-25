<?php

namespace App\Console\Commands;

use App\Helpers\TeletextHelper;
use App\Models\Teletext;
use Illuminate\Console\Command;

class TeletextUpdateThumbnail extends Command
{

    protected $signature = 'teletext:update-thumbnail {id}';


    public function __construct()
    {
        parent::__construct();
    }


    public function handle()
    {
        $teletext = Teletext::find($this->argument('id'));
        TeletextHelper::takeScreenshot($teletext);
    }
}
