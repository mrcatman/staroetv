<?php

namespace App\Crossposting\Services\Odnoklassniki;

use App\Crossposting\Crossposter;
use App\Crossposting\Post;
use Illuminate\Support\Facades\Http;

class OdnoklassnikiCrossposter extends Crossposter {

    protected $params = [
        'id' => 'odnoklassniki',
        'public_name' => 'Одноклассники',
        'can_auto_connect' => true,
        'can_edit_posts' => false,
        'can_edit_comments' => false,
    ];

    private $base_url = "http://api.odnoklassniki.ru/fb.do";


    public function __construct() {
        $this->config = new OdnoklassnikiConfigManager($this);
    }

    public function getPostInstance(): Post {
        return new OdnoklassnikiPost();
    }

    public function isActive(): bool {
        return (bool)$this->config->get("access_token");
    }

    public function getAutoConnectRedirectURI(): string {
        $client_id = $this->config->get('app_id');
        if (!$client_id) {
            throw new \Exception("Не указан id приложения");
        }
        $redirect_uri = urlencode("https://staroetv.su/crosspost/redirect/odnoklassniki");
        $scope = "VALUABLE_ACCESS,GROUP_CONTENT,VIDEO_CONTENT,PHOTO_CONTENT,LONG_ACCESS_TOKEN";
        return "https://connect.ok.ru/oauth/authorize?client_id=$client_id&scope=$scope&response_type=token&redirect_uri=$redirect_uri";
    }

    private function uploadPicture(string $path) {
        $group_id = $this->config->get('group_id');
        if (!$group_id) {
            throw new \Exception("Не указан id группы");
        }
        $upload_url = $this->request("photosV2.getUploadUrl", [
            'gid' => $group_id
        ]);
        $upload_url = $upload_url['upload_url'];


        if ($path[0] == "/") {
            $picture = public_path($path);
        } else {
            $rnd = md5(random_bytes(16));
            $path = public_path("pictures/temp/".$rnd);
            file_put_contents($path, file_get_contents($path));
        }

        $extension = pathinfo($picture, PATHINFO_EXTENSION);
        $data = Http::attach(
            'pic1',
            fopen($picture, 'r'),
            'photo.'.$extension
        )->post($upload_url)->json();

        return $data['photos'][array_keys($data['photos'])[0]]['token'];
    }

    public function request($method, $params) {
        $token = $this->config->get("access_token");
        if (!$token) {
            throw new \Exception("Не указан токен, возможно вы не авторизовались. Нажмите кнопку 'Подключить'");
        }

        $key = $this->config->get("public_key");
        if (!$key) {
            throw new \Exception("Не указан ключ приложения");
        }

        $params["application_key"] = $key;
        $params["method"] = $method;
        $params["sig"] = $this->calcSignature($method, $params);
        $params['access_token'] = $token;

        return Http::post($this->base_url, $params)->json();
    }

    private function calcSignature($methodName, $params = []): string{
        $key = $this->config->get("public_key");
        $secret_key = $this->config->get("secret_key");
        $token = $this->config->get("access_token");

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

    public function getRequestParams(?Post $post, $media = null) {
        if ($post) {
            $text = $post->getParam('text');
            $link = $post->getParam('link');
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
        $group_id = $this->config->get('group_id');
        return [
            'type' => 'GROUP_THEME',
            'gid' => $group_id,
            'attachment' => json_encode([
                'media' => $json_attachment
            ])
        ];
    }

    public function createPost(Post $post) {
        if (!$post instanceof OdnoklassnikiPost) {
            throw new \Exception("Неверный объект поста");
        }

        $post_ids = [];

        $media = $post->getParam('media');
        $params = $this->getRequestParams($post, count($media) > 0 ? $media[0] : null);
        $response = $this->request('mediatopic.post', $params);
        $post_ids[] = $response;

        if (count($media) > 1) {
            for ($i = 1; $i < count($media); $i++) {
                $params = $this->getRequestParams(null, $media[$i]);
                $response = $this->request('mediatopic.post', $params);
                $post_ids[] = $response;
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

    public function makeLinks(string $id): array {
        $group_name = $this->config->get('group_name');
        if (!$group_name) {
            return [];
        }

        $post_ids = explode(";", $id);
        $list = [];
        foreach ($post_ids as $post_id) {
            $list[] = "https://ok.ru/".$group_name."/topic/".$post_id;
        }
        return $list;
    }

}
