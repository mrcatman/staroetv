<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

class CutVideo implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $path,
        private readonly string $output_path,
        private readonly int $start,
        private readonly int $end
    ) { }

    public function handle(): void
    {
        $storage = Storage::disk('media-storage');
        $upload_path = $storage->path($this->upload_path);

        $extension = pathinfo($upload_path, PATHINFO_EXTENSION);

        $mp4_path = str_replace("." . $extension, ".mp4", $this->new_path);
        Process::forever()->run("ffmpeg -y -i $this->path -c:v libx264 -acodec copy -ss $this->start -to $this->end $output");

        $storage->delete($this->upload_path);
    }
}
