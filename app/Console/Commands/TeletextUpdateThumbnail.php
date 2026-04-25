<?php

namespace App\Console\Commands;

use App\Helpers\CSVHelper;
use App\Helpers\TeletextHelper;
use App\Models\Channel;
use App\Models\Picture;
use App\Models\Teletext;
use App\Models\User;
use App\Models\UserMeta;
use App\Models\UserWarning;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

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
