<?php

namespace App\Console\Commands;

use App\Models\Record;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class EnableWebmForConvertedVideos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:enable-webm-for-converted-videos {--confirm}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $storage = Storage::disk('media-storage');
        $files = $storage->allFiles('videos');

        $progressBar = $this->output->createProgressBar(count($files));
        $progressBar->start();

        foreach ($files as $file) {
            $progressBar->advance();

            if (!str_ends_with($file, '.mp4')) {
                continue;
            }

            $webm_path = str_replace('.mp4', '.webm', $file);
                try {
                    $webm_exists = $storage->exists($webm_path);
                    if (!$webm_exists) {
                        continue;
                    }
            } catch (\Exception $e) {
                echo "Exception: ".$e->getMessage() . "\n";
                continue;


            $last_modified = $storage->lastModified($webm_path);
            if (time() - $last_modified < 60) { // в процессе конвертации
                echo "\nConverting now: {$file}\n";
                continue;
            }

            $video = Record::where('source_path', 'LIKE', "%{$file}%")->first();
            if (!$video) {
                echo "\nNot found: {$file}\n";
                continue;
            }

            echo "\n{$video->id}: {$video->title}\n";

            if ($this->option('confirm')) {
                $storage->delete($file);
                $video->use_webm = true;
                $video->save();
            }
        }

        $progressBar->finish();
    }
}
