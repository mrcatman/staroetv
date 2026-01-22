<?php

namespace App\Jobs;

use App\Helpers\MediaHelper;
use App\Models\Picture;
use App\Models\Record;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
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
        $record = $this->record;
        $path = "videos/" . $record->id . ".mp4";
        $url = $record->original_url;


        $temp_path = public_path($path);
        $output_path = $storage->path($path);

        $process = Process::run("yt-dlp -f 'mp4' -i '$url' --output $temp_path");
        var_dump($process->output(), $process->errorOutput());
        if (file_exists($temp_path)) {
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

            Cache::forget('record_' . $record->id);
            Cache::forget('record_cover_' . $record->id);
        }

    }
}
