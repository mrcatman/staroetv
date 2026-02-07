<?php

namespace App\Crossposting;

use App\Crossposting\Services\Discord\DiscordCrossposter;
use App\Crossposting\Services\Facebook\FacebookCrossposter;
use App\Crossposting\Services\Odnoklassniki\OdnoklassnikiCrossposter;
use App\Crossposting\Services\Telegram\TelegramCrossposter;
use App\Crossposting\Services\Twitter\TwitterCrossposter;
use App\Crossposting\Services\VK\VKCrossposter;
use App\Helpers\ExternalServicesHelper;
use App\Models\Record;
use App\Models\SocialPost;

class CrossposterManager {

    private $list = [
        'vk' => VKCrossposter::class,
        'telegram' => TelegramCrossposter::class,
        'twitter' => TwitterCrossposter::class,
        'odnoklassniki' => OdnoklassnikiCrossposter::class,
        'discord' => DiscordCrossposter::class,
        'facebook' => FacebookCrossposter::class
    ];

    /**
     * @return Crossposter[]
     */
    public function getList(): array {
        return array_map(function($item) { return new $item; }, $this->list);
    }

    public function get($name): ?Crossposter {
        if (isset($this->list[$name])) {
            return new $this->list[$name];
        }
        return null;
    }

    public function publish()
    {
        $posts = SocialPost::where('post_ts', '<', time())->get();
        foreach ($posts as $post) {
            $post_connections = $post->postConnections;
            foreach ($post_connections as $post_connection) {
                if ($post_connection->status == -1) {
                    $this->publishPost($post, $post_connection);
                    usleep(500000);
                }
            }
        }
    }

    public function publishPost($crosspost, $post_connection) {
        $service = $post_connection->service;
        $crossposter = $this->get($service);
        if (!$crossposter) {
            return [
                'status' => 0,
                'text' => 'Ошибка: кросспостер не найден'
            ];
        }

        $data = $crosspost->post_data;
        $post = $crossposter->getPostInstance();

        $text = $this->getText($data, $service);
        $post->setParam('text', $text);

        $link = $this->getLink($data, $service);
        if ($link) {
            $post->setParam('link', $link);
        }

        if (isset($data['media']) && count($data['media']) > 0) {
            if ($service == "vk" || $service == "odnoklassniki") {
                foreach ($data['media'] as &$media_item) {
                     if ($media_item['type'] == "video" ) {
                         if (isset($media_item['value_alt']) && trim($media_item['value_alt']) != "") {
                             $media_item['value'] = $media_item['value_alt'];
                         } elseif (strpos($media_item['value'], "staroetv.su/video") !== false) {
                             $val = explode("/", $media_item['value']);
                             $id = $val[count($val) - 1];
                             $video = Record::find($id);
                             if ($video->embed_code != "") {
                                 if ($youtube_video_id = ExternalServicesHelper::resolveYoutubeId($video->embed_code)) {
                                     $media_item['value'] = "https://youtube.com/watch?v=$youtube_video_id";
                                 }
                             }
                         }
                     }
                }
            }
            $post->setParam('media', $data['media']);
        }
        if ($post_connection->status === 1 && !request()->input('force')) {
            $last_data = $post_connection->last_data;
            $old_text = $this->getText($last_data, $service);
            $old_link = $this->getLink($last_data, $service);

            $need_update_media = [];
            $count_old = isset($last_data['media']) ? count($last_data['media']) : 0;
            $count_new = isset($data['media']) ? count($data['media']) : 0;
            $count = $count_new > $count_old ? $count_new : $count_old;
            for ($i = 0; $i < $count; $i++) {
                $old_media = isset($last_data['media']) && isset($last_data['media'][$i]) ? $last_data['media'][$i] : null;
                $new_media = isset($data['media']) && isset($data['media'][$i]) ? $data['media'][$i] : null;
                $need_update_media[] = $new_media != $old_media;
            }
            $post->setFieldsToUpdate([
                'text' => $old_text != $text,
                'link' => $old_link != $link,
                'media' => $need_update_media
            ]);

            try {
                $post_ids =  $crossposter->editPost($post_connection->post_ids, $post);
                $post_connection->post_ids = $post_ids;
                $post_connection->last_data = $data;
                $post_connection->error_log = null;
                $post_connection->save();

                $status = 1;
            } catch (\Exception $e) {
                $post_connection->error_log = $e->getTraceAsString();
                $post_connection->save();
                $status = 0;
            }
        } else {
            $status = null;
            try {
                $post_ids = $crossposter->createPost($post);
                $post_connection->post_ids = $post_ids;
                $post_connection->status = 1;
                $post_connection->last_data = $data;
                $post_connection->save();
                $status = 1;
            } catch (\Exception $e) {
                $post_connection->error_log = $e->getMessage();
                $post_connection->status = 0;
                $post_connection->save();
                $status = 0;
            }
        }
        return [
            'status' => $status,
            'data' => [
                'post_connection' => $post_connection
            ]
        ];
    }


    protected function getText($data, $service) {
        $text = "";
        if ($service == "twitter" && (isset($data['short_texts']) && count($data['short_texts'] ) > 0)) {
            $texts = array_filter(array_map(function($text) {
                return trim($text);
            }, $data['short_texts']));
            if (count($texts) > 0) {
                return $texts;
            }
        }
        if (isset($data['text']) && $data['text'] != '') {
            $text = $data['text'];
        }
        return $text;
    }

    protected function getLink($data, $service){
        $link = null;
        if (isset($data['link']) && $data['link'] != "") {
            $link = $data['link'];
            if (isset($data['link_text']) && $data['link_text'] != "") {
                $link = [$data['link'], $data['link_text']];
            }
        }
        return $link;
    }

}
