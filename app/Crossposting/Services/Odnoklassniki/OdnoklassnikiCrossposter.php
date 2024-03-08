<?php

namespace App\Crossposting\Services\Odnoklassniki;

use App\Crossposting\BaseCrossposter;

class OdnoklassnikiCrossposter extends BaseCrossposter {

    public $id = "odnoklassniki";
    public $public_name = "Одноклассники";
    public $can_auto_connect = true;
    public $can_edit_posts = false;
    public $can_delete_posts = false;

    protected $base_url = "http://api.odnoklassniki.ru/fb.do";


    public function __construct() {
        parent::__construct();
        $this->settings_manager = new OdnoklassnikiSettingsManager($this);
    }

    public function getPostInstance() {
        return new OdnoklassnikiPost();
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
        $redirect_uri = urlencode("https://staroetv.su/crosspost/redirect/odnoklassniki");
        $scope = "VALUABLE_ACCESS,GROUP_CONTENT,VIDEO_CONTENT,PHOTO_CONTENT,LONG_ACCESS_TOKEN";
        $url = "https://connect.ok.ru/oauth/authorize?client_id=$client_id&scope=$scope&response_type=token&redirect_uri=$redirect_uri";
        return $url;
    }

    protected function uploadPicture($picture) {
        $group_id = $this->settings_manager->get('group_id');
        if (!$group_id) {
            throw new \Exception("Не указан id группы");
        }
        $upload_url = $this->request("photosV2.getUploadUrl", [
            'gid' => $group_id
        ]);
        $upload_url = $upload_url['upload_url'];


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
                    'name'     => 'pic1',
                    'contents' => fopen($picture, 'r'),
                    'filename' => 'photo.'.$extension
                ]
            ]
        ]);
        $upload_data = json_decode($upload->getBody()->getContents(), 1);
        $token = $upload_data['photos'][array_keys($upload_data['photos'])[0]]['token'];
        return $token;
    }

    public function request($method, $params) {
        $token = $this->settings_manager->get("access_token");
        if (!$token) {
            throw new \Exception("Не указан токен, возможно вы не авторизовались. Нажмите кнопку 'Подключить'");
        }
        $key = $this->settings_manager->get("public_key");
        if (!$key) {
            throw new \Exception("Не указан ключ приложения");
        }
        $params["application_key"] = $key;
        $params["method"] = $method;
        $params["sig"] = $this->calcSignature($method, $params);
        $params['access_token'] = $token;
        $requestStr = "";
        foreach($params as $key=>$value){
            $requestStr .= $key . "=" . urlencode($value) . "&";
        }
        $requestStr = substr($requestStr, 0, -1);
        $curl = curl_init($this->base_url . "?" . $requestStr);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $s = curl_exec($curl);
        curl_close($curl);
        return json_decode($s, true);
    }

    private function calcSignature($methodName, $params = []){
        $key = $this->settings_manager->get("public_key");
        $secret_key = $this->settings_manager->get("secret_key");
        $token = $this->settings_manager->get("access_token");
        $params["application_key"] = $key;
        $params["method"] = $methodName;
        $requestStr = "";
        ksort($params);
        foreach($params as $key=>$value){
            $requestStr .= $key . "=" . $value;
        }
        $requestStr .= md5($token . $secret_key);
        return md5($requestStr);
    }

    public function getRequestParams($post, $media = null) {
        if ($post) {
            $text = $post->getText();
            $link = $post->getLinkText();
            if ($link != "") {
                $text.= PHP_EOL.PHP_EOL.$link;
            }
        } else {
            $text = isset($media['text']) && $media['text'] != "" ? $media['text'] : "...";
        }
        $json_attachment = [
            [
                'type' => 'text',
                'text' => $text
            ]
        ];
        if ($media) {
            if ($media['type'] == "video") {
                $json_attachment[] = [
                    'type' => 'link',
                    'url' => $media['value']
                ];
            } elseif ($media['type'] == "picture") {
                $json_attachment[] = [
                    'type' => 'photo',
                    'list' => [
                        [
                            'id' => $this->uploadPicture($media['value'])
                        ]
                    ]
                ];
            }
        }
        $group_id = $this->settings_manager->get('group_id');
        $params = [
            'type' => 'GROUP_THEME',
            'gid' => $group_id,
            'attachment' => json_encode([
                'media' => $json_attachment
            ])
        ];
        return $params;
    }

    public function createPost($post) {
        if (!$post instanceof OdnoklassnikiPost) {
            throw new \Exception("Неверный объект поста");
        }
        $post_ids = [];

        $media = $post->getMedia();
        $params = $this->getRequestParams($post, count($media) > 0 ? $media[0] : null);
        $response = $this->request('mediatopic.post', $params);
        $ids[] = $response;

        if (count($media) > 1) {
            for ($i = 1; $i < count($media); $i++) {
                $params = $this->getRequestParams(null, $media[$i]);
                $response = $this->request('mediatopic.post', $params);
                $ids[] = $response;
            }
        }
        return implode(";", $post_ids);
    }

    public function editPost($id, $post) {
        throw new \Exception("Невозможно редактировать посты, внесите изменения вручную");
    }

    public function deletePost($id) {
        throw new \Exception("Невозможно удалять посты, сделайте это вручную");
    }

    public function makeLinks($post_ids) {
        $post_ids = explode(";", $post_ids);
        $group_name = $this->settings_manager->get('group_name');
        if (!$group_name) {
            return [];
        }
        $list = [];
        foreach ($post_ids as $post_id) {
            $list[] = "https://ok.ru/".$group_name."/topic/".$post_id;
        }
        return $list;
    }

}
