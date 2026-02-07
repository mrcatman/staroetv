<?php
namespace App\Helpers;

use App\Models\Picture;
use App\Models\Record;

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

    public static function resolveYoutubeChannelId($url)
    {
        preg_match('~(?:https?://)?(?:www\.)?(?:youtube\.com/(?:c/|channel/|user/|@)?|youtu\.be/)([\w@\-]{1,30})~', $url, $matches);
        if (!isset($matches[1])) {
            return null;
        }
        return $matches[1];
    }

    public static function resolveVkChannelId($url){
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

    public static function resolveVkId($url) {
        preg_match('/(.*)oid=(.*)&id=(.*)&hash(.*)/', $url, $output);
        if (!isset($output[2]) || !isset($output[3])) {
            return null;
        }
        return $output[2] ."_". $output[3];
    }

    public static function resolveYoutubeId($url) {
        preg_match('/^.*((m\.)?youtu\.be\/|vi?\/|u\/\w\/|embed\/|\?vi?=|\&vi?=)([^#\&\?]*).*/', $url, $matches);
        if (!isset($matches[3])) {
            return null;
        }
        return $matches[3];
    }

    public static function resolveId($url) {
        if ($youtube_id = self::resolveYoutubeId($url)) {
            return $youtube_id;
        }
        if ($vk_id = self::resolveVkId($url)) {
            return $vk_id;
        }
        return null;
    }

    public static function youtubeVideo($youtube_video_id, $part = 'snippet'){
        $token = config('tokens.youtube');
        return self::request("https://www.googleapis.com/youtube/v3/videos?id=$youtube_video_id&key=$token&part=$part");
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

    public static function getThumbnail(Record $record) {
        if ($record->original_url && (strpos($record->original_url, 'vk.com') !== false || strpos($record->original_url, 'vkvideo.ru') !== false) && !$record->source_path) {
            preg_match('/(.*?)video_ext.php\?oid=(.*?)&id=(.*?)&hash=(.*?)[&"](.*?)/', $record->embed_code, $matches);

            $user_id = $matches[2];
            $video_id = $matches[3];
            $hash = $matches[4];
            $data = self::vkVideo($user_id . "_" . $video_id . "_" . $hash);

            if (isset($data->response) && count($data->response->items) > 0) {
                $video = $data->response->items[0];
                return $video->image[count($video->image) - 2]->url;
            }
        } elseif ($youtube_id = self::resolveYoutubeId($record->original_url)) {
            return 'https://i.ytimg.com/vi/' . $youtube_id . '/hqdefault.jpg';
        }

        return null;
    }

}
