<?php

namespace App\Jobs;

use App\Helpers\MediaHelper;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
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
        $output_path = $storage->path("videos/{$this->filename}.mp4");
        MediaHelper::cutVideo($this->path, $this->start, $this->end, $output_path);
    }
}
