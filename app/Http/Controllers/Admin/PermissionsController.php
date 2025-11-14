<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Permissions;
use App\Helpers\PermissionsHelper;
use App\Http\Controllers\ArticlesController;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Channel;
use App\Models\Genre;
use App\Models\HistoryEvent;
use App\Models\Page;
use App\Models\Program;
use App\Models\Record;
use App\Models\Smile;
use App\Models\User;
use App\Models\UserGroup;
use App\Models\UserGroupConfig;
use App\Models\UserReputation;
use Illuminate\Support\Facades\Hash;

class PermissionsController extends Controller {


    public function index() {
        $permissions_values = UserGroupConfig::get()->groupBy('option_name');
        $groups = UserGroup::all();

        $permissions = Permissions::LIST;
        $default_groups = Permissions::DEFAULT_USER_GROUPS;
        return view("pages.admin.permissions", [
            'permissions' => $permissions,
            'permissions_values' => $permissions_values,
            'groups' => $groups,
            'default_groups' => $default_groups
        ]);
    }

    public function save() {
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
