<?php
namespace App\Helpers;

use App\Models\Record;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;

class MediaServerHelper {


    private static function url($path) {
        return config('site.media_server_url') . '/api/'. $path;
    }

    public static function ffprobe($path)
    {
        $response = Http::get(self::url('ffprobe'), [
            'video' => $path,
        ])->object();
        if (isset($response->error)) {
            throw new \Exception($response->error);
        }
        return $response;
    }

    public static function download($url, $id)
    {
        $response = Http::post(self::url('download'), [
            'url' => $url,
            'id' => $id,
        ])->object();

        if (isset($response->error)) {
            throw new \Exception($response->error);
        }
        return $response;
    }

    public static function playlist($url, $range)
    {
        $response = Http::get(self::url('playlist'), [
            'url' => $url,
            'range' => $range,
        ])->object();

        if (isset($response->error)) {
            throw new \Exception($response->error);
        }

        return $response->data->playlist;
    }

    public static function getDownloadUrl($id, $url) {
        $response = Http::get(self::url('download-url'), [
            'id' => $id,
            'url' => $url,
        ])->object();
        if (isset($response->error)) {
            throw new \Exception($response->error);
        }

        return $response->data->url;
    }


}
