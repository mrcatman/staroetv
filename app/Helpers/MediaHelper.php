<?php
namespace App\Helpers;

use App\Models\Record;
use Illuminate\Support\Facades\Http;

class MediaHelper {

    public static function findDuration(Record $record) {
        if ($record->use_own_player) {
            $path = str_replace('videos/', '', $record->source_path);
            $response = json_decode(Http::get('https://media.staroetv.su/api/ffprobe?video='.urlencode($path))->getBody()->getContents());
            if (isset($response->error)) {
                throw new \Exception($response->error);
            }
            $record->length = $response->result->streams[0]->duration;
            $record->save();
        } elseif (strpos($record->embed_code, "youtu") !== false) {
            preg_match('/youtube.com\/embed\/(.*?)"/', $record->embed_code, $output);
            $video_id = $output[1];
            $token = config('tokens.youtube');
            $video_details = json_decode(Http::get("https://www.googleapis.com/youtube/v3/videos?id=$video_id&part=contentDetails&&key=$token")->getBody()->getContents());
            $di = new \DateInterval($video_details->items[0]->contentDetails->duration);
            $reference = new \DateTimeImmutable();
            $endTime = $reference->add($di);

            $record->length = $endTime->getTimestamp() - $reference->getTimestamp();
            $record->save();
        }
    }

    public function makeThumbnail($path, $seconds = null): string
    {
        $filename = pathinfo($path, PATHINFO_FILENAME);

        $frames = (int)shell_exec("ffprobe -v error -select_streams v:0 -show_entries stream=nb_frames -of default=nokey=1:noprint_wrappers=1 $path");
        //$middle = floor($frames / 2);
        $fps = (int)shell_exec("ffprobe -v error -select_streams v -of default=noprint_wrappers=1:nokey=1 -show_entries stream=r_frame_rate $path");
        $fps = (int)explode("/", $fps)[0];
        if ($fps > 100 || $fps === 0) {
            $fps = 30;
        }
        $thumbnail_time = ($frames / $fps) - 3;
        $thumbnail_path = public_path("video_covers/$filename.jpg");

        $thumbnail_command = "ffmpeg -y -ss $thumbnail_time -i '$path' -vframes 1 '" . $thumbnail_path . "'";
        shell_exec($thumbnail_command);

        return $thumbnail_path;
    }

}
