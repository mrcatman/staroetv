<?php

namespace App\Console\Commands;

use App\Helpers\CSVHelper;
use App\Helpers\MediaHelper;
use App\Helpers\TeletextHelper;
use App\Models\Channel;
use App\Models\Picture;
use App\Models\Record;
use App\Models\Teletext;
use App\Models\User;
use App\Models\UserMeta;
use App\Models\UserWarning;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class SetDuration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'records:duration {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parse media files durations';

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
        $record = Record::findOrFail($this->argument('id'));
        MediaHelper::findDuration($record);
        echo 'Duration: '.$record->length.PHP_EOL;
    }
}
