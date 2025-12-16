<?php
namespace App\Helpers;

class ExternalServicesHelper {

    private static function request($url)
    {
        $data = file_get_contents($url);
        return json_decode($data);
        //$data = json_decode(shell_exec(" curl ''"));

    }
    public static function vkVideo($vk_video_id)
    {
        $token = config('tokens.vk');
        return self::request("https://api.vk.com/method/video.get?access_token=$token&v=5.130&videos=$vk_video_id&extended=1");
    }

    public static function youtubeVideo($youtube_video_id)
    {
        $token = config('tokens.youtube');
        return self::request("https://www.googleapis.com/youtube/v3/videos?id=$youtube_video_id&key=$token&part=snippet");
    }
}
