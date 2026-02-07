<?php

namespace App\Crossposting;

class Crossposter {

    protected $params = [
        'id' => '',
        'public_name' => '',
        'can_auto_connect' => false,
        'can_edit_posts' => false,
        'can_edit_comments' => false,
    ];

    protected ConfigManager $config;

    public function getParam(string $param): mixed
    {
        if (isset($this->params[$param])) {
            return $this->params[$param];
        }
        return null;
    }

    public function getSettingsList()
    {
        return $this->config->getSettingsList();
    }

    public function saveConfig(mixed $data): void
    {
       $this->config->saveSettingsFromRequest($data);
    }


    public function getPostInstance(): Post {
        throw new \Exception("Not implemented");
    }

    public function isActive(): bool {
        return false;
    }


    public function getAutoConnectRedirectURI(): string {

    }

    public function afterRedirect($data) {

    }


    public function createPost(Post $post) {
        throw new \Exception("Not implemented");
    }

    public function editPost(string|int $id, Post $post) {
        throw new \Exception("Not implemented");
    }

    public function deletePost(string|int $id) {
        throw new \Exception("Not implemented");
    }

    public function makeLinks(string $id): array {
        return [];
    }


    protected function getPictureFullUrl(string $url) {
        if ($url && $url[0] == "/") {
            $url = "https://staroetv.su" . $url;
        }
        return $url;
    }
}
