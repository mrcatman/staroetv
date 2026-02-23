<?php

namespace App\Jobs;

use App\Helpers\MediaHelper;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

class CutVideo implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $path,
        private readonly string $filename,
        private readonly float $start,
        private readonly float $end
    ) { }

    public function handle(): void
    {
        $storage = Storage::disk('media-storage');
        $temp_path = public_path("temp_videos/{$this->filename}.mp4");
        $output_path = $storage->path("videos/{$this->filename}.mp4");
        MediaHelper::cutVideo($this->path, $this->start, $this->end, $temp_path);
        Process::forever()->run("mv $temp_path $output_path");
    }
}
