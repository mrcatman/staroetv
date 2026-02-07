<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

class ConvertVideo implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $upload_path,
        private readonly string $new_path,
    ) { }

    public function handle(): void
    {
        $storage = Storage::disk('media-storage');
        $upload_path = $storage->path($this->upload_path);

        $extension = pathinfo($upload_path, PATHINFO_EXTENSION);

        $mp4_path = str_replace("." . $extension, ".mp4", $this->new_path);
        Process::forever()->run("ffmpeg -y -i " . $upload_path . " -strict -2 " . $mp4_path);

        $storage->delete($this->upload_path);
    }
}
