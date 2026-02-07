<?php

namespace App\Jobs;

use App\Helpers\MediaHelper;
use App\Models\VideoCut;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class DownloadExternalVideoForCut implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly VideoCut $cut,
        private readonly string $url,
        private readonly string $output_path
    )
    {
    }

    public function handle(): void
    {
        $process = MediaHelper::download($this->url, $this->output_path);
        if (strpos($process->output(), ".mkv") !== false) {
            $mkv_path = str_replace(".mp4", ".mkv", $this->output_path);
            MediaHelper::reencode($mkv_path, $this->output_path);
        }

        $url = config('app.url').route('cut.on-downloaded', ['id' => $this->cut->id, 'status' => $process->successful() ? '1' : '0']);
        Http::get($url);
    }
}
