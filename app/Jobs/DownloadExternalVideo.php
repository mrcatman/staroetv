<?php

namespace App\Jobs;

use App\Helpers\ExternalServicesHelper;
use App\Helpers\MediaHelper;
use App\Helpers\MediaServerHelper;
use App\Models\Picture;
use App\Models\Record;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

class DownloadExternalVideo implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Record $record,
    ) {}

    public function handle(): void
    {
        $storage = Storage::disk('media-storage');
        $temp = Storage::disk('temp');
        $record = $this->record;
        $path = "videos/" . $record->id . ".mp4";
        $url = ExternalServicesHelper::resolveDownloadUrl($record->embed_code);

        $temp_path = $temp->path($path);
        $output_path = $storage->path($path);

        if (str_contains($url, 'youtu')) {
            MediaServerHelper::download($url, $record->id);
            return;
        }

        $process = MediaHelper::download($url, $temp_path);
        if ($process->errorOutput()) {
            throw new \Exception($process->errorOutput());
        }
        if (file_exists($temp_path)) {
            return;
        }
        $thumbnail = MediaHelper::makeThumbnail($temp_path);
        $cover = Picture::firstOrNew([
            'url' => $thumbnail
        ]);
        $cover->save();

        Process::run("mv $temp_path $output_path");

        $record->use_own_player = true;
        $record->source_type = "local";
        $record->source_path = "/" . $path;
        $record->cover_id = $cover->id;
        $record->save();

        $record->clearCache();
    }
}
