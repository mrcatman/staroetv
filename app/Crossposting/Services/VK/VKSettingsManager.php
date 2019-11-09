<?php

namespace App\Crossposting\Services\VK;

use App\Crossposting\BaseSettingsManager;

class VKSettingsManager extends BaseSettingsManager {

    protected $settings = [
        ["id" => "group_id", "visible" => false],
        ["id" => "access_token", "name" => "Access token"],
        ["id" => "app_id", "name" => "ID приложения"]
    ];

    public function filterSettingsBeforeGet($settings) {
        $group_id = $this->get("group_id");
        $group_url = $this->get("group_url");
        $settings[] = [
            "id" => "group_url",
            "name" => "Ссылка на группу",
            "value" => ($group_url && $group_url != "") ? $group_url : (($group_id && $group_id != "") ? "https://vk.com/group".$this->get("group_id") : "")
        ];
        return parent::filterSettingsBeforeGet($settings);
    }


    public function saveSettingsFromRequest($data) {
        if (isset($data['group_url']) && $data['group_url'] != "") {
            $group_url = str_replace("https://vk.com/", "", $data['group_url']);
            if ($group_url != "") {
                $resolve = $this->crossposter->request("utils.resolveScreenName", [
                    'screen_name' => $group_url
                ], false);
                if (!isset($resolve->response->type) || $resolve->response->type !== "group") {
                    throw new \Exception("Такой группы не существует");
                }
                $this->set('group_url', $data['group_url']);
                $this->set('group_id', $resolve->response->object_id);
            }
        }
        return parent::saveSettingsFromRequest($data);
    }

}