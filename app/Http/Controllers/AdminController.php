<?php

namespace App\Http\Controllers;

use App\Channel;
use App\ChannelName;
use App\Comment;
use App\Helpers\CommentsHelper;
use App\Program;
use App\Smile;
use App\UserAward;
use App\UserGroup;
use App\UserGroupConfig;
use App\UserReputation;
use App\Record;
use Carbon\Carbon;

class AdminController extends Controller {

    public function getPermissions() {
        $permissions_values = UserGroupConfig::get()->groupBy('option_name');
        $groups = UserGroup::all();
        $config = config('usergroups');
        $permissions = $config['parameters'];
        $default_groups = $config['default_groups'];
        return view("pages.admin.permissions", [
            'permissions' => $permissions,
            'permissions_values' => $permissions_values,
            'groups' => $groups,
            'default_groups' => $default_groups
        ]);
    }


    public function getChannels() {
        $channels = Channel::with('logo')->get();
        return view("pages.admin.channels", [
            'channels' => $channels
        ]);
    }

    public function getSmiles() {
        $smiles = Smile::with('picture')->get();
        return view("pages.admin.smiles", [
            'smiles' => $smiles
        ]);
    }

    public function getChannelsOrder() {
        $channels = Channel::with('logo')->orderBy('order', 'ASC')->get();
        return view("pages.admin.channels_order", [
            'channels' => $channels
        ]);
    }

    public function setChannelsOrder() {
        foreach (request()->input('order') as $channel_id => $order) {
            Channel::where(['id' => $channel_id])->update(['order' => $order]);
        }
        return [
            'status' => 1,
            'text' => 'Сохранено',
        ];
    }

    public function saveSmiles() {
        $smiles = collect(request()->input('smiles'));
        $ids = $smiles->pluck('id')->toArray();
        foreach ($smiles as $smile) {
            $smile['show_in_panel'] = isset($smile['show_in_panel']) && ($smile['show_in_panel'] == "1" || $smile['show_in_panel'] == "true");
            if (isset($smile['picture']) && isset($smile['picture']['id'])) {
                $smile['picture_id'] = $smile['picture']['id'];
            }
            unset($smile['picture']);
            unset($smile['created_at']);
            unset($smile['updated_at']);
            if (isset($smile['id'])) {
                $smile_obj = Smile::find($smile['id']);
                $smile_obj->fill($smile);
                $smile_obj->save();
            } else {
                $smile_obj = new Smile($smile);
                $smile_obj->save();
                $ids[] = $smile_obj->id;
            }
        }
        Smile::whereNotIn('id', $ids)->delete();
        $all_smiles = Smile::with('picture')->get();
        return [
            'status' => 1,
            'text' => 'Сохранено',
            'data' => [
                'smiles' => $all_smiles
            ]
        ];
    }

    public function savePermissions() {
        $permissions = collect(json_decode(request()->input('permissions'), 1));
        $permissions_to_add = $permissions->filter(function($permission) {
            return !isset($permission['id']);
        })->map(function ($permission) {
            return [
                'option_name' => $permission['permission_id'],
                'option_value' => $permission['value'],
                'group_id' => $permission['group_id']
            ];
        })->toArray();
        UserGroupConfig::insert($permissions_to_add);
        $existing = UserGroupConfig::all()->pluck('option_value', 'id');
        foreach ($permissions as $permission) {
            if (isset($permission['id'])) {
                if ($permission['value'] != $existing->get($permission['id'])) {
                    UserGroupConfig::where(['id' => $permission['id']])->update(['option_value' => $permission['value']]);
                }
            }
        }
        return ['status' => 1, 'text' => 'Сохранено'];
    }

}
