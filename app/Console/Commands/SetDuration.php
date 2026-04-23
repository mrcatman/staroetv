<?php

namespace App\Console\Commands;

use App\Helpers\CSVHelper;
use App\Helpers\ExternalServicesHelper;
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

    protected $signature = 'records:duration {id?}';


    protected $description = 'Parse media files durations';


    public function __construct()
    {
        parent::__construct();
    }

    private function updateDuration($record) {
        echo "Updating duration for $record->title ($record->id)".PHP_EOL;
        $duration = MediaHelper::updateDuration($record);
        if ($duration) {
            echo "Updated duration to $duration".PHP_EOL;
        } else {
            echo "Cannot resolve duration".PHP_EOL;
        }
    }


    public function handle()
    {
        if ($this->argument('id')) {
            $record = Record::where(['id' => $this->argument('id')])->firstOrFail();
            $this->updateDuration($record);
        } else {
            Record::whereNull('length')->inRandomOrder(1)->chunk(100, function ($records) {
                foreach ($records as $record) {
                    $this->updateDuration($record);
                    usleep(500000);
                }
            });
        }



    }
}
