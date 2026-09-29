<?php

namespace App\Jobs;

use App\Helpers\MediaHelper;
use App\Models\Record;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

class ProcessUploadedRecord implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Record $record,
        private readonly string $uploaded_file_path,
    ) { }

    public function handle(): void
    {
        $temp_storage = Storage::disk('temp');
        $media_storage = Storage::disk('media-storage');

        $temp_path = $temp_storage->path($this->uploaded_file_path);
        $codec = MediaHelper::getCodec($temp_path);

        $extension = $this->record->is_radio ? '.mp3' : ($codec === 'h264' ? '.mp4' : '.webm');

        $filename = $this->record->id . $extension;
        $file_path = $this->record->is_radio ? "/radio-recordings/$filename" : "/videos/$filename";
        $new_path = $media_storage->path($file_path);

        $allowed_codecs = $this->record->is_radio ? ['mp3'] : ['h264', 'vp8', 'vp9'];

        if (in_array($codec, $allowed_codecs)) {
            Log::debug("Moving record to storage without processing: $temp_path -> $file_path, codec: $codec");
            Process::forever()->run("mv $temp_path $new_path");
        } else {
            Log::debug("Processing record: $temp_path -> $file_path, original codec: $codec, allowed codecs: " . implode(', ', $allowed_codecs));

            $temp_converted_path = $temp_storage->path($this->uploaded_file_path . '.converted'. $extension);

            // todo: check other codecs besides x264
            $this->record->is_radio ? MediaHelper::reencodeAudio($temp_path, $temp_converted_path) : MediaHelper::reencode($temp_path, $temp_converted_path);

            Log::debug("Running: mv $temp_converted_path $new_path");
            Process::forever()->run("mv $temp_converted_path $new_path");
        }

        $this->record->source_path = $file_path;
        $this->record->is_converting = false;
        $this->record->save();
        $this->record->clearCache();
    }
}
