<?php

namespace App\Crossposting\Services\VK;

use App\Crossposting\Crossposter;
use App\Crossposting\Post;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class VKCrossposter extends Crossposter {

    protected $params = [
        'id' => 'vk',
        'public_name' => 'ВК',
        'can_auto_connect' => true,
        'can_edit_posts' => true,
        'can_edit_comments' => true,
    ];

    private $base_url = "https://api.vk.com/method/";
    private $version = "5.103";


    public function __construct() {
        $this->config = new VKConfigManager($this);
    }

    public function getPostInstance(): Post {
        return new VKPost();
    }

    public function isActive(): bool {
        return !!$this->config->get("access_token");
    }

    public function getAutoConnectRedirectURI(): string {
        $client_id = $this->config->get('app_id');
        if (!$client_id) {
            throw new \Exception("Не указан id приложения");
        }

        $redirect_uri = urlencode("https://oauth.vk.com/blank.html");
        return "https://oauth.vk.com/authorize?client_id=$client_id&redirect_uri=$redirect_uri&display=page&scope=335888&response_type=token&v=".$this->version."&revoke=1";
    }

    private function apiRequest(string $url, mixed $params, bool $group_params = true) {
        $token = $this->config->get("access_token");
        if (!$token) {
            throw new \Exception("Не указан токен, возможно вы не авторизовались. Нажмите кнопку 'Подключить'");
        }
        $params['access_token'] = $token;
        if ($group_params) {
            $group_id = $this->config->get('group_id');
            if (!$group_id) {
                throw new \Exception("Не указан id группы");
            }
            $params['from_group'] = 1;
            $params['owner_id'] = "-".$group_id;

        }
        $params['v'] = $this->version;
        $request_url = $this->base_url.$url;

        return Http::post($request_url, $params)->json();
    }

    private function getPostText(VKPost $post): string {
       $text = $post->getParam('text');
       $link = $post->getParam('link_text');
       if ($link != "") {
           $text.= PHP_EOL.PHP_EOL.$link;
       }
       return $text;
    }

    private function isOnePost(VKPost $post) {
        $media = $post->getParam('text');
        $is_one_post = true;
        foreach ($media as $media_item) {
            if ($media_item['type'] == "video" && strpos($media_item['value'], "youtu") === false) {
                $is_one_post = false;
            }
        }
        return $is_one_post;
    }

    public function createPost(Post $post) {
        if (!$post instanceof VKPost) {
            throw new \Exception("Неверный объект поста");
        }

        $text = $this->getPostText($post);
        $link = $post->getParam('link_value');

        $media = $post->getParam('media');
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
            $attachments_string = $this->uploadMediaItem($post, $media[0]);
            $params['attachments'] = $attachments_string;
        }
        $response = $this->apiRequest("wall.post", $params);
        if (isset($response->error)) {
            throw new \Exception("Ошибка: ".$response->error->error_msg);
        }
        if ($multiple_posts) {
            $post_ids = [$response->response->post_id];
            for ($i = 1; $i < count($media); $i++) {
                $params = $this->getRequestParams($post, $media[$i]);
                $response = $this->apiRequest("wall.post", $params);
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
                $response = $this->apiRequest("wall.post", $params);
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
            $media_id = $this->uploadMediaItem($post, $media);
            $params['attachments'] = $media_id;
        }
        return $params;
    }

    public function editPost($id, $post) {
        $id = explode(";", $id);
        if (!$post instanceof VKPost) {
            throw new \Exception("Неверный объект поста");
        }
        $text = $this->getPostText($post);
        $media = $post->getParam('media');
        $params = [
            'post_id' => $id[0],
            'message' => $text,
        ];
        if ($this->isOnePost($post)) {
            $attachments_string = $this->uploadMedia($post, $media);
            $params['attachments'] = $attachments_string;
        } else {
            $attachments_string = $this->uploadMediaItem($post, $media[0]);
            $params['attachments'] = $attachments_string;
        }

        $response = $this->apiRequest("wall.edit", $params);
        if (isset($response->error)) {
            throw new \Exception("Ошибка: ".$response->error->error_msg);
        }
        if (!$this->isOnePost($post)) {
            $need_update_media = $post->needUpdateField('media');
            for ($i = 1; $i < count($need_update_media); $i++) {
                if ($need_update_media[$i]) {
                    if (isset($media[$i])) {
                        if (isset($id[$i])) {
                            $params = [
                                'post_id' => $id[0],
                                'message' => $text,
                                'attachments' => $attachments_string
                            ];
                            $this->apiRequest("wall.edit", $params);
                        } else {
                            $params = [
                                'message' => $text,
                                'attachments' => $attachments_string
                            ];
                            $response = $this->apiRequest("wall.post", $params);
                            if (isset($response->error)) {
                                throw new \Exception("Ошибка: ".$response->error->error_msg);
                            }
                            $id[] = $response->response->post_id;
                        }
                    } else {
                        $post_to_delete_id = $id[$i];
                        $params = [
                            'post_id' => $post_to_delete_id,
                        ];
                        $this->apiRequest("wall.delete", $params);
                        $id = array_filter($id, function($post_id) use ($post_to_delete_id) {
                            return $post_id != $post_to_delete_id;
                        });
                    }
                }
            }
        }
        return implode(";", $id);
    }

    public function deletePost(int|string $id): void {
        $post_ids = explode(";", $id);
        foreach ($post_ids as $post_id) {
            $response = $this->apiRequest("wall.delete",  [
                'post_id' => $post_id,
            ]);
            if (isset($response->error)) {
                throw new \Exception("Ошибка: ".$response->error->error_msg);
            }
        }
    }

    public function makeLinks(string $id): array {
        $group_id = $this->config->get('group_id');
        if (!$group_id) {
            return [];
        }

        $post_ids = explode(";", $id);
        $list = [];
        foreach ($post_ids as $post_id) {
            $list[] = "https://vk.com/wall-".$group_id."_".$post_id;
        }
        return $list;
    }

    private function uploadVideo($video): string {
        $ids = [];
        $group_id = $this->config->get('group_id');
        if (!$group_id) {
            throw new \Exception("Не указан id группы");
        }
        $response = $this->apiRequest("video.save", [
            'group_id' => $group_id,
            'link' => $video
        ]);

        Http::get($response->response->upload_url);

        $ids[] = "video-".$group_id."_".$response->response->video_id;
        return implode(",",$ids);
    }

    protected function uploadPicture(string $path) {
        $group_id = $this->config->get('group_id');
        if (!$group_id) {
            throw new \Exception("Не указан id группы");
        }
        $server = $this->apiRequest("photos.getWallUploadServer", [
            'group_id' => $group_id
        ]);
        $upload_url = $server->response->upload_url;
        if ($path[0] == "/") {
            $picture = public_path($path);
        } else {
            $rnd = md5(random_bytes(16));
            $path = public_path("pictures/temp/".$rnd);
            file_put_contents($path, file_get_contents($path));
        }

        $extension = pathinfo($picture, PATHINFO_EXTENSION);

        $data = Http::attach(
            'photo',
            fopen($picture, 'r'),
            'photo.'.$extension
        )->post($upload_url)->json();
        $save = $this->apiRequest("photos.saveWallPhoto", [
            'photo' => $data->photo,
            'server' => $data->server,
            'hash' => $data->hash,
            'group_id' => $group_id
        ]);
        return "photo".$save->response[0]->owner_id."_".$save->response[0]->id;
    }

    public function uploadMediaItem(VKPost $post, $media_item) {
        $cache_value = Cache::get('crosspost-media-'.$media_item['value']);
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
                Cache::put('crosspost-media-'.$media_item['value'], $media_id, 3600);
                return $media_id;
            }
        }
    }

    private function uploadMedia(VKPost $post, $media, bool $add_link = true) {
        $media_ids = [];
        foreach ($media as $media_item) {
            $media_id = $this->uploadMediaItem($post, $media_item);
            if ($media_id) {
               $media_ids[] = $media_id;
           }
        }
        if ($add_link) {
            $link = $post->getParam('link_value');
            if ($link) {
                $media_ids[] = $link;
            }
        }
        return implode(",", $media_ids);
    }
}
