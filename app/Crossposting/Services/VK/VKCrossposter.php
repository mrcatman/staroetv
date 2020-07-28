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
        $scope = 335876;
        $url = "https://oauth.vk.com/authorize?client_id=$client_id&redirect_uri=$redirect_uri&display=page&scope=$scope&response_type=token&v=".$this->version."&revoke=1";
        return $url;
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

    public function createPost($post) {
        if (!$post instanceof VKPost) {
            throw new \Exception("Неверный объект поста");
        }
        $text = $post->getText();
        $link = $post->getLink();
        $picture = $post->getPicture();

        $attachments_string = "";
        if ($picture != "") {
            $picture_id = $this->uploadPicture($picture);
            $attachments_string.= $picture_id.",";
        }
        if ($link != "") {
            $attachments_string.= $link;
        }
        $params = [
            'message' => $text,
            'attachments' => $attachments_string
        ];
        $response = $this->request("wall.post", $params);
        if (isset($response->error)) {
            throw new \Exception("Ошибка: ".$response->error->error_msg);
        }
        return $response->response->post_id;
    }

    public function editPost($id, $post) {
        if (!$post instanceof VKPost) {
            throw new \Exception("Неверный объект поста");
        }
        $text = $post->getText();
        $link = $post->getLink();
        $picture = $post->getPicture();

        $attachments_string = "";
        if ($picture != "" && $post->needChangePicture()) {
            $picture_id = $this->uploadPicture($picture);
            $attachments_string.= $picture_id.",";
        }
        if ($link != "") {
            $attachments_string.= $link;
        }
        $params = [
            'post_id' => $id,
            'message' => $text,
            'attachments' => $attachments_string
        ];
        $response = $this->request("wall.edit", $params);
        if (isset($response->error)) {
            throw new \Exception("Ошибка: ".$response->error->error_msg);
        }
        return $response->response->post_id;
    }

    public function deletePost($id) {
        $params = [
            'post_id' => $id,
        ];
        $response = $this->request("wall.delete", $params);
        if (isset($response->error)) {
            throw new \Exception("Ошибка: ".$response->error->error_msg);
        }
        return $id;
    }

    public function makeLinkById($post_id) {
        $group_id = $this->settings_manager->get('group_id');
        if (!$group_id) {
            throw new \Exception("Не указан id группы");
        }
        return "https://vk.com/wall-".$group_id."_".$post_id;
    }

}
