<?php

namespace App\Http\Controllers;

use App\Channel;
use App\ChannelName;
use App\Comment;
use App\Helpers\CommentsHelper;
use App\Program;
use App\UserAward;
use App\UserGroup;
use App\UserGroupConfig;
use App\UserReputation;
use App\Video;
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
