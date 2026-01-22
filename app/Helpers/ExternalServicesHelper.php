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

    public static function vkVideoList($vk_owner_id, $count = 100, $offset = 0) {
        $token = config('tokens.vk');
        return self::request("https://api.vk.com/method/video.get?count=$count&offset=$offset&access_token=$token&v=5.101&owner_id=$vk_owner_id&extended=1");
    }

    public static function resolveVkId($url){
        preg_match('~(?:https?://)?(?:vk\.com|vkvideo\.ru|vkontakte\.ru)/(?:@?[a-zA-Z0-9_\.\-]*)?(\d+)(?:$|[/?#])~', $url, $matches);
        if (isset($matches[1])) {
            return $matches[1];
        }

        preg_match('~(?:https?://)?(?:vk\.com|vkvideo\.ru|vkontakte\.ru)/(@?[a-zA-Z0-9_\.\-]+)~', $url, $matches);
        if (!isset($matches[1])) {
            return null;
        }

        $id = $matches[1];
        if (is_numeric($id)) {
            return $id;
        }

        if (str_starts_with($id, '@')) {
            $id = substr($id, 1);
        }

        $token = config('tokens.vk');
        $data = self::request("https://api.vk.com/method/utils.resolveScreenName?screen_name=$id&access_token=$token&v=5.101");
        if (isset($data->response->object_id)) {
            return $data->response->type == 'group' ? -$data->response->object_id : $data->response->object_id;
        }
        return null;
    }

    public static function resolveYoutubeId($url){
        preg_match('~(?:https?://)?(?:www\.)?(?:youtube\.com/(?:c/|channel/|user/|@)?|youtu\.be/)([\w@\-]{1,30})~', $url, $matches);
        if (!isset($matches[1])) {
            return null;
        }

        return $matches[1];
    }

    public static function youtubeVideo($youtube_video_id){
        $token = config('tokens.youtube');
        return self::request("https://www.googleapis.com/youtube/v3/videos?id=$youtube_video_id&key=$token&part=snippet");
    }

    public static function youtubeVideoList($youtube_owner_id, $count = 100, $offset = 0) {
        $token = config('tokens.youtube');

        $playlist_data = json_decode(file_get_contents("https://www.googleapis.com/youtube/v3/channels?part=contentDetails&forUsername=$youtube_owner_id&key=$token"));
        $uploads_playlist_id = $playlist_data->items[0]->contentDetails->relatedPlaylists->uploads ?? null;
        if (!$uploads_playlist_id) {
            $playlist_data = json_decode(file_get_contents("https://www.googleapis.com/youtube/v3/channels?part=contentDetails&forHandle=$youtube_owner_id&key=$token"));
            $uploads_playlist_id = $playlist_data->items[0]->contentDetails->relatedPlaylists->uploads ?? null;
        }


        if (!$uploads_playlist_id) {
            throw new \Exception('Не найден плейлист загрузок');
        }
        $youtube_url = "https://www.googleapis.com/youtube/v3/playlistItems?playlistId=$uploads_playlist_id&key=$token&part=snippet&maxResults=$count";
        if ($offset != '') {
            $youtube_url.="&pageToken=".$offset;
        }
        return self::request($youtube_url);
    }

}
