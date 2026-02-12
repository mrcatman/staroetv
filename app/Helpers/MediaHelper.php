<?php
namespace App\Helpers;

use App\Models\Record;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;

class MediaHelper {

    public static function mediaServerFfprobe($path)
    {

        $response = Http::get('https://media.staroetv.su/api/ffprobe?video=' . urlencode($path))->getBody()->getContents()->json();
        if (isset($response->error)) {
            throw new \Exception($response->error);
        }
        return $response;
    }

    public static function makeThumbnail($path, $time = null): string
    {
        $filename = pathinfo($path, PATHINFO_FILENAME);

        if ($time === null) {
            $frames = (int)shell_exec("ffprobe -v error -select_streams v:0 -show_entries stream=nb_frames -of default=nokey=1:noprint_wrappers=1 $path");
            //$middle = floor($frames / 2);
            $fps = (int)shell_exec("ffprobe -v error -select_streams v -of default=noprint_wrappers=1:nokey=1 -show_entries stream=r_frame_rate $path");
            $fps = (int)explode("/", $fps)[0];
            if ($fps > 100 || $fps === 0) {
                $fps = 30;
            }
            $time = ($frames / $fps) - 3;
        }

        $thumbnail_path = "video_covers/$filename.jpg";
        $thumbnail_full_path = public_path($thumbnail_path);
        Process::run("ffmpeg -y -ss $time -i '$path' -vframes 1 '$thumbnail_full_path'");
var_dump("ffmpeg -y -ss $time -i '$path' -vframes 1 '$thumbnail_full_path'");
        return "/$thumbnail_path";
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
        return "youtube-dl -f 'bestvideo[ext=mp4]+bestaudio[ext=m4a]/mp4' -i '$url' --output $path";
    }

    public static function reencode($path, $output_path) {
        return Process::run("ffmpeg -y -i $path -c:v libx264 $output_path && rm $path");
    }

    public static function download($url, $path)
    {
        return Process::forever()->run(self::getDownloadCommand($url, $path));
    }
}
