<?php

namespace App\Jobs;

use App\Helpers\MediaHelper;
use App\Helpers\MediaServerHelper;
use App\Models\VideoCut;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

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
        try {
            if (str_contains($this->url, 'youtu')) {
                MediaServerHelper::download($this->url, 'cut_' . $this->cut->id);
            } else {
                $process = MediaHelper::download($this->url, $this->output_path);
                if ($process->errorOutput()) {
                    throw new \Exception($process->errorOutput());
                }

                if (str_contains($process->output(), ".mkv")) {
                    $mkv_path = str_replace(".mp4", ".mkv", $this->output_path);
                    MediaHelper::reencode($mkv_path, $this->output_path);
                }
                if (!file_exists($this->output_path)) {
                    throw new \Exception("Downloaded file not found");
                }
                $this->cut->updateMediaParams();
                $this->cut->download_status = VideoCut::STATUS_SUCCESS;
                $this->cut->error = null;
                $this->cut->save();
            }
        } catch (\Exception $e) {
            $this->cut->download_status = VideoCut::STATUS_ERROR;
            $this->cut->error = $e->getMessage();
            $this->cut->save();
        }
    }
}
