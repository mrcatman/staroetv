<?php
namespace App\Helpers;

use App\Models\Record;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;

class MediaHelper {

    public static function mediaServerFfprobe($path)
    {
        $response = Http::get('https://media.staroetv.su/api/ffprobe?video=' . urlencode($path))->json();
        if (isset($response->error)) {
            throw new \Exception($response->error);
        }
        return $response;
    }

    public static function mediaServerDownload($url, $id)
    {
        $response = Http::post("https://media.staroetv.su/api/download", [
            'url' => $url,
            'id' => $id,
        ])->json();

        if (isset($response->error)) {
            throw new \Exception($response->error);
        }
        return $response;
    }


    public static function makeThumbnail($path, $time = null): string
    {
        $filename = pathinfo($path, PATHINFO_FILENAME);

        if ($time === null) {
            $frames = (int)Process::run("ffprobe -v error -select_streams v:0 -show_entries stream=nb_frames -of default=nokey=1:noprint_wrappers=1 $path")->output();
            //$middle = floor($frames / 2);
            $fps = (int)Process::run("ffprobe -v error -select_streams v -of default=noprint_wrappers=1:nokey=1 -show_entries stream=r_frame_rate $path")->output();
            $fps = (int)explode("/", $fps)[0];
            if ($fps > 100 || $fps === 0) {
                $fps = 30;
            }
            $time = ($frames / $fps) - 3;
        }

        $thumbnail_path = "video_covers/$filename.jpg";
        $thumbnail_full_path = public_path($thumbnail_path);
        Process::run("ffmpeg -y -ss $time -i '$path' -vframes 1 '$thumbnail_full_path'");
        return "/$thumbnail_path";
    }

    public static function getDuration($path): int {
        return (int)Process::run("ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 $path")->output();
    }

    public function getFps($path): int
    {
        $fps = Process::run("ffprobe -v error -select_streams v -of default=noprint_wrappers=1:nokey=1 -show_entries stream=r_frame_rate $path")->output();
        $fps_data = explode("/", $fps);
        if (count($fps) === 2) {
            return round((int)$fps_data[0] / (int)$fps_data[1]);
        } else {
            return (int)$fps_data[0];
        }
    }

    public static function getFrames($path): int {
        return (int)Process::run("ffprobe -v error -select_streams v:0 -show_entries stream=nb_frames -of default=nokey=1:noprint_wrappers=1 $path")->output();
    }

    public static function getDownloadCommand($url, $path) {
        return "yt-dlp -f 'bestvideo[ext=mp4]+bestaudio[ext=m4a]/mp4' -i '$url' --output $path";
    }

    public static function reencode($path, $output_path) {
        return Process::run("ffmpeg -y -i $path -c:v libx264 $output_path && rm $path");
    }

    public static function download($url, $path)
    {
        return Process::forever()->run(self::getDownloadCommand($url, $path));
    }

    public static function updateDuration(Record $record) {
        if ($record->use_own_player) {
            $response = self::mediaServerFfprobe(str_replace('videos/', '', $record->source_path));
            $record->length = $response->result->streams[0]->duration;
            $record->save();

            return $record->length;
        }

        if ($youtube_video_id = ExternalServicesHelper::resolveYoutubeId($record->embed_code)) {
            $duration = ExternalServicesHelper::youtubeVideoDuration($youtube_video_id);
            $record->length = $duration;
            $record->save();
            return $record->length;
        }

        if ($vk_video_id = ExternalServicesHelper::resolveVkId($record->embed_code)) {
            $response = ExternalServicesHelper::vkVideo($vk_video_id);
            $record->length = $response->response->items[0]->duration;
            $record->save();
            return $record->length;
        }

        return null;
    }
}
