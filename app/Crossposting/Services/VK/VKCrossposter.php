<?php

namespace App\Crossposting\Services\VK;

use App\Crossposting\BaseCrossposter;

class VKCrossposter extends BaseCrossposter {

    public $id = "vk";
    public $public_name = "ВК";
    public $can_auto_connect = true;

    protected $base_url = "https://api.vk.com/method/";
    protected $version = "5.103";


    public function __construct() {
        parent::__construct();
        $this->settings_manager = new VKSettingsManager($this);
    }

    public function getPostInstance() {
        return new VKPost();
    }

    public function isActive() {
        $token = $this->settings_manager->get("access_token");
        return (bool)$token;
    }

    public function getAutoConnectRedirectURI() {
        $client_id = $this->settings_manager->get('app_id');
        if (!$client_id) {
            throw new \Exception("Не указан id приложения");
        }
        $redirect_uri = urlencode("https://oauth.vk.com/blank.html");
        $scope = 335892;
        $url = "https://oauth.vk.com/authorize?client_id=$client_id&redirect_uri=$redirect_uri&display=page&scope=$scope&response_type=token&v=".$this->version."&revoke=1";
        return $url;
    }



    public function request($url, $params, $group_params = true) {
        $token = $this->settings_manager->get("access_token");
        if (!$token) {
            throw new \Exception("Не указан токен, возможно вы не авторизовались. Нажмите кнопку 'Подключить'");
        }
        $params['access_token'] = $token;
        if ($group_params) {
            $group_id = $this->settings_manager->get('group_id');
            if (!$group_id) {
                throw new \Exception("Не указан id группы");
            }
            $params['from_group'] = 1;
            $params['owner_id'] = "-".$group_id;

        }
        $params['v'] = $this->version;
        $request_url = $this->base_url.$url;
        $res = $this->client->request('POST', $request_url, [
            'form_params' => $params,
        ]);
        return json_decode($res->getBody()->getContents());
    }

    protected function getPostText($post) {
       $text = $post->getText();
       $link = $post->getLinkText();
       if ($link != "") {
           $text.= PHP_EOL.PHP_EOL.$link;
       }
       return $text;
    }

    protected function isOnePost($post) {
        $media = $post->getMedia();
        $is_one_post = true;
        foreach ($media as $media_item) {
            if ($media_item['type'] == "video" && strpos($media_item['value'], "youtu") === false) {
                $is_one_post = false;
            }
        }
        return $is_one_post;
    }

    public function createPost($post) {
        if (!$post instanceof VKPost) {
            throw new \Exception("Неверный объект поста");
        }
        $text = $this->getPostText($post);
        $link = $post->getLinkValue();
        $media = $post->getMedia();
        $params = [
            'message' => $text,
        ];
        $multiple_posts = false;
        if (count($media) == 0) {
            if ($link) {
                $params['attachments'] = $link;
            }
        } elseif ($this->isOnePost($post)) {
            $attachments_string = $this->uploadMedia($post, $media);
            $params['attachments'] = $attachments_string;
        } else {
            $multiple_posts = true;
            $attachments_string = $this->uploadOneMediaItem($post, $media[0]);
            $params['attachments'] = $attachments_string;
        }
        $response = $this->request("wall.post", $params);
        if (isset($response->error)) {
            throw new \Exception("Ошибка: ".$response->error->error_msg);
        }
        if ($multiple_posts) {
            $post_ids = [$response->response->post_id];
            for ($i = 1; $i < count($media); $i++) {
                $params = $this->getRequestParams($post, $media[$i]);
                $response = $this->request("wall.post", $params);
                if (isset($response->error)) {
                    throw new \Exception("Ошибка: ".$response->error->error_msg);
                }
                $post_ids[] = $response->response->post_id;
            }
            $link = $post->getLinkValue();
            if ($link && $link != "") {
                $text = $post->getLinkText();
                $params = [
                    'message' => $text,
                    'attachments' => $link
                ];
                $response = $this->request("wall.post", $params);
                if (isset($response->error)) {
                    throw new \Exception("Ошибка: ".$response->error->error_msg);
                }
                $post_ids[] = $response->response->post_id;
            }
            return implode(";", $post_ids);
        } else {
            return $response->response->post_id;
        }
    }

    public function getRequestParams($post, $media) {
        $text = isset($media['text']) && $media['text'] != "" ? $media['text'] : "...";
        $params = [
            'message' => $text
        ];
        if ($media['type'] == "video" && strpos($media['value'], "youtu") === false) {
            $params['message'] = $text.PHP_EOL.PHP_EOL.$media['value'];
            $params['attachments'] = $media['value'];
        } else {
            $media_id = $this->uploadOneMediaItem($post, $media);
            $params['attachments'] = $media_id;
        }
        return $params;
    }

    public function editPost($post_ids, $post) {
        $post_ids = explode(";", $post_ids);
        if (!$post instanceof VKPost) {
            throw new \Exception("Неверный объект поста");
        }
        $text = $this->getPostText($post);
        $media = $post->getMedia();
        $params = [
            'post_id' => $post_ids[0],
            'message' => $text,
        ];
        if ($this->isOnePost($post)) {
            $attachments_string = $this->uploadMedia($post, $media);
            $params['attachments'] = $attachments_string;
        } else {
            $attachments_string = $this->uploadOneMediaItem($post, $media[0]);
            $params['attachments'] = $attachments_string;
        }

        $response = $this->request("wall.edit", $params);
        if (isset($response->error)) {
            throw new \Exception("Ошибка: ".$response->error->error_msg);
        }
        if (!$this->isOnePost($post)) {
            $need_update_media = $post->needUpdateField('media');
            for ($i = 1; $i < count($need_update_media); $i++) {
                if ($need_update_media[$i]) {
                    if (isset($media[$i])) {

                        if (isset($post_ids[$i])) {
                            $params = [
                                'post_id' => $post_ids[0],
                                'message' => $text,
                                'attachments' => $attachments_string
                            ];
                            $this->request("wall.edit", $params);
                        } else {
                            $params = [
                                'message' => $text,
                                'attachments' => $attachments_string
                            ];
                            $response = $this->request("wall.post", $params);
                            if (isset($response->error)) {
                                throw new \Exception("Ошибка: ".$response->error->error_msg);
                            }
                            $post_ids[] = $response->response->post_id;
                        }
                    } else {
                        $post_to_delete_id = $post_ids[$i];
                        $params = [
                            'post_id' => $post_to_delete_id,
                        ];
                        $response = $this->request("wall.delete", $params);
                        $post_ids = array_filter($post_ids, function($post_id) use ($post_to_delete_id) {
                            return $post_id != $post_to_delete_id;
                        });
                    }
                }
            }
        }
        return implode(";", $post_ids);
    }

    public function deletePost($post_ids) {
        $post_ids = explode(";", $post_ids);
        foreach ($post_ids as $post_id) {
            $params = [
                'post_id' => $post_id,
            ];
            $response = $this->request("wall.delete", $params);
            if (isset($response->error)) {
                throw new \Exception("Ошибка: ".$response->error->error_msg);
            }
        }
        return $post_ids;
    }

    public function makeLinks($post_ids) {
        $post_ids = explode(";", $post_ids);
        $group_id = $this->settings_manager->get('group_id');
        if (!$group_id) {
            return [];
        }
        $list = [];
        foreach ($post_ids as $post_id) {
            $list[] = "https://vk.com/wall-".$group_id."_".$post_id;
        }
        return $list;
    }

    public function uploadVideo($video) {
        $ids = [];
        $group_id = $this->settings_manager->get('group_id');
        if (!$group_id) {
            throw new \Exception("Не указан id группы");
        }
        $response = $this->request("video.save", [
            'group_id' => $group_id,
            'link' => $video
        ]);
        $ch = curl_init($response->response->upload_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch);
        $ids[] = "video-".$group_id."_".$response->response->video_id;

        return implode(",",$ids);
    }

    protected function uploadPicture($picture) {
        $group_id = $this->settings_manager->get('group_id');
        if (!$group_id) {
            throw new \Exception("Не указан id группы");
        }
        $server = $this->request("photos.getWallUploadServer", [
            'group_id' => $group_id
        ]);
        $upload_url = $server->response->upload_url;
        if ($picture[0] == "/") {
            $picture = public_path($picture);
        } else {
            $rnd = md5(random_bytes(16));
            $path = public_path("pictures/temp/".$rnd);
            file_put_contents($path, file_get_contents($picture));
            $picture = $path;
        }
        $extension = pathinfo($picture, PATHINFO_EXTENSION);
        $upload = $this->client->request('POST', $upload_url, [
            'multipart' => [
                [
                    'name'     => 'photo',
                    'contents' => fopen($picture, 'r'),
                    'filename' => 'photo.'.$extension
                ]
            ]
        ]);
        $upload_data = json_decode($upload->getBody()->getContents());
        $save = $this->request("photos.saveWallPhoto", [
            'photo' => $upload_data->photo,
            'server' => $upload_data->server,
            'hash' => $upload_data->hash,
            'group_id' => $group_id
        ]);
        $id = "photo".$save->response[0]->owner_id."_".$save->response[0]->id;
        return $id;
    }

    public function uploadOneMediaItem($post, $media_item) {
        $cache_value = $post->getMediaCacheForUrl($media_item['value']);
        if ($cache_value) {
            return $cache_value;
        } else {
            $media_id = null;
            if ($media_item['type'] == "video") {
                if (strpos($media_item['value'], "youtu") === false) {
                    $media_id = $media_item['value'];
                } else {
                    $media_id = $this->uploadVideo($media_item['value']);
                }
            } elseif ($media_item['type'] == "picture") {
                $media_id = $this->uploadPicture($media_item['value']);
            }
            usleep(500000);
            if ($media_id) {
                $post->setMediaCacheForUrl($media_item['value'], $media_id);
                return $media_id;
            }
        }
    }
    public function uploadMedia($post, $media, $add_link = true) {
        $media_ids = [];
        foreach ($media as $media_item) {
            $media_id = $this->uploadOneMediaItem($post, $media_item);
            if ($media_id) {
               $media_ids[] = $media_id;
           }
        }
        if ($add_link) {
            $link = $post->getLinkValue();
            if ($link) {
                $media_ids[] = $link;
            }
        }
        return implode(",", $media_ids);
    }
}
