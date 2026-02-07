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
        if ($record->use_own_player) {
            $response = MediaHelper::mediaServerFfprobe(str_replace('videos/', '', $record->source_path));
            $record->length = $response->result->streams[0]->duration;
            $record->save();

            echo 'Duration (got from media server): '.$record->length.PHP_EOL;
            return;
        }

        if ($youtube_video_id = ExternalServicesHelper::resolveYoutubeId($record->embed_code)) {
            $response = ExternalServicesHelper::youtubeVideo($youtube_video_id, 'contentDetails');

            $interval = new \DateInterval($response->items[0]->contentDetails->duration);
            $reference = new \DateTimeImmutable();
            $endTime = $reference->add($interval);
            $record->length = $endTime->getTimestamp() - $reference->getTimestamp();
            $record->save();

            echo 'Duration (got from Youtube API): '.$record->length.PHP_EOL;
            return;
        }

        if ($vk_video_id = ExternalServicesHelper::resolveVkId($record->embed_code)) {
            $response = ExternalServicesHelper::vkVideo($vk_video_id);
            $record->length = $response->response->items[0]->duration;
            $record->save();

            echo 'Duration (got from VK API): '.$record->length.PHP_EOL;
            return;
        }

        echo "Cannot resolve duration";
    }
}
