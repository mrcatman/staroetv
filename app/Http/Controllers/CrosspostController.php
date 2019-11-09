<?php

namespace App\Http\Controllers;


use App\Crossposting\CrossposterResolver;

class CrosspostController extends Controller {

    public function getServices() {
        $resolver = new CrossposterResolver();
        $list = $resolver->getList();

        $services = [];

        foreach ($list as $service_name) {
            $service = new $service_name;
            $services[] = [
                'id' => $service->id,
                'name' => $service->public_name,
                'is_active' => $service->isActive(),
                'can_auto_connect' => $service->can_auto_connect,
                'settings' => $service->settings_manager->getSettingsList()
            ];
        }
        return view("pages.admin.crossposting", [
            'services' => $services
        ]);
    }

    public function autoconnect($name) {
        $crossposter = (new \App\Crossposting\CrossposterResolver())->get($name);
        if (!$crossposter || !$crossposter->can_auto_connect) {
            abort(403);
        }
        return redirect($crossposter->getAutoConnectRedirectURI());
    }

    public function saveSettings($name) {
        $crossposter = (new \App\Crossposting\CrossposterResolver())->get($name);
        if (!$crossposter) {
            abort(403);
        }
        $crossposter->settings_manager->saveSettingsFromRequest(request()->all());
        return ['status' => 1, 'text' => 'Сохранено'];
    }
}