<?php

namespace App\Http\Controllers;


use App\Crossposting\CrossposterManager;
use App\Models\SocialPost;
use App\Models\SocialPostConnection;
use Carbon\Carbon;

class CrosspostController extends Controller {

    public function __construct(
        private CrossposterManager $manager
    ) {}

    protected function getServicesList() {

        $services = [];

        foreach ($this->manager->getList() as $service) {
            $services[] = [
                'id' => $service->getParam('id'),
                'name' => $service->getParam('public_name'),
                'is_active' => true, // $service->isActive(),
                'can_auto_connect' => $service->getParam('can_auto_connect'),
                'can_edit_posts' => $service->getParam('can_edit_posts'),
                'can_delete_posts' => $service->getParam('can_delete_posts'),
                'settings' => $service->getSettingsList()
            ];
        }
        return $services;
    }

    public function getServices() {
        $services = $this->getServicesList();
        return view("pages.admin.crossposting", [
            'services' => $services
        ]);
    }

    public function autoconnect($name) {
        $crossposter = $this->manager->get($name);
        if (!$crossposter || !$crossposter->getParam('can_auto_connect')) {
            abort(403);
        }
        return redirect($crossposter->getAutoConnectRedirectURI());
    }

    public function afterRedirect($name) {
        $crossposter = $this->manager->get($name);
        if (!$crossposter || !$crossposter->getParam('can_auto_connect')) {
            abort(403);
        }
        $crossposter->afterRedirect(request()->all());
        return redirect(route('admin.crossposting'));
    }

    public function saveSettings($name) {
        $crossposter = $this->manager->get($name);
        if (!$crossposter) {
            abort(403);
        }
        $crossposter->saveConfig(request()->all());
        return ['status' => 1, 'text' => 'Сохранено'];
    }

    public function index() {
        $crossposts = SocialPost::orderBy('id', 'desc')->paginate(24);
        return view("pages.crossposting.list", [
            'crossposts' => $crossposts,
        ]);
    }

    public function add() {
        $services = $this->getServicesList();
        return view("pages.crossposting.form", [
            'crosspost' => null,
            'services' => $services
        ]);
    }

    public function save() {
        $crosspost = new SocialPost();
        return $this->fillData($crosspost);
    }

    public function edit($id) {
        $services = $this->getServicesList();
        $crosspost = SocialPost::find($id);
        return view("pages.crossposting.form", [
            'crosspost' => $crosspost,
            'services' => $services
        ]);
    }

    public function update($id) {
        $crosspost = SocialPost::find($id);
        return $this->fillData($crosspost);
    }

    private function fillData($crosspost) {
        $data = request()->validate([
            'data.text' => 'required',
            'data.short_text' => 'sometimes',
            'data.short_texts' => 'sometimes|array',
            'data.link' => 'sometimes',
            'data.link_text' => 'sometimes',
            'data.media' => 'sometimes|array'
        ]);
        if (isset($data['short_texts'])) {
            $data['short_texts'] = array_filter($data['short_texts'], function($text) {
                return $text && mb_strlen($text, "UTF-8") > 0;
            });
        }
        $crosspost->post_data_old = $crosspost->post_data;
        $crosspost->post_data = $data['data'];
        $crosspost->user_id = auth()->user()->id;
        if (request()->has('post_time') && request()->input('post_time') && request()->input('post_time') !== "1970-01-01T00:00:00.000Z") {
            $crosspost->post_ts = Carbon::createFromFormat('d/m/Y H:i', request()->input('post_time'))->timestamp;
            $crosspost->post_ts = $crosspost->post_ts - 60 * 60 * 7;
        } else {
            $crosspost->post_ts = null;
        }
        $crosspost->save();
        $services = request()->input('services', []);
        SocialPostConnection::where(['crosspost_id' => $crosspost->id])->whereNotIn('service', $services)->delete();
        foreach ($services as $service) {
            $post_connection = SocialPostConnection::firstOrNew([
                'crosspost_id' => $crosspost->id,
                'service' => $service
            ]);
            if (!$post_connection->exists) {
                $post_connection->status = -1;
                $post_connection->save();
            }
        }
        return [
            'status' => 1,
            'text' => 'Сохранено',
            'redirect_to' => '/crossposts/'.$crosspost->id.'/edit',
            'data' => [
                'crosspost' => $crosspost
            ]
        ];
    }

    public function delete() {
        $crosspost = SocialPost::find(request()->input('crosspost_id'));
        if (!$crosspost) {
            return ['status' => 0, 'text' => 'Пост не найден'];
        }
        SocialPostConnection::where(['crosspost_id' => $crosspost->id])->delete();
        $crosspost->delete();
        return ['status' => 1, 'text' => 'Удалено', 'redirect_to' => '/crossposts'];
    }

    public function makePost($id, $service) {
        $crosspost = SocialPost::find($id);
        if (!$crosspost) {
            return ['status' => 0, 'text' => 'Пост не найден'];
        }
        $post_connection = SocialPostConnection::firstOrNew([
            'crosspost_id' => $crosspost->id,
            'service' => $service
        ]);
        return $this->manager->publishPost($crosspost, $post_connection);
    }

    public function deletePost($id, $name) {
        $crosspost = SocialPost::find($id);
        if (!$crosspost) {
            return ['status' => 0, 'text' => 'Пост не найден'];
        }

        $crossposter = $this->manager->get($name);
        if (!$crossposter) {
            return [
                'status' => 0,
                'text' => 'Ошибка: кросспостер не найден'
            ];
        }
        $post_connection = SocialPostConnection::firstOrNew([
            'crosspost_id' => $crosspost->id,
            'service' => $name
        ]);
        if ($post_connection->post_ids) {
            $crossposter->deletePost($post_connection->post_ids);
            $post_connection->status = -1;
            $post_connection->save();
            return [
                'status' => 1
            ];
        } else {
            return [
                'status' => 0,
                'text' => 'Не найден пост для удаления'
            ];
        }
    }


}
