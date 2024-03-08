<?php

namespace App\Http\Controllers;

use App\User;
use App\UserGroup;
use App\UserGroupConfig;

class UserGroupsController extends Controller {

    public function destroy($id) {
        $group_to_move = request()->input('group_to_move',2);
        User::where(['group_id' => $group_to_move])->update(['group_id' => $group_to_move]);
        UserGroup::find($id)->delete();
        UserGroupConfig::where(['group_id' => $id])->delete();
        return ['status' => 1, 'text' => 'Удалено'];
    }

    public function store() {
        $group = new UserGroup();
        if (request()->has('name') && request()->input('name') != "") {
            $group->name = request()->input('name');
            if (request()->has('icon')) {
                $group->icon = request()->input('icon');
            }
            if (request()->has('icon_svg_code')) {
                $group->icon = request()->input('icon_svg_code');
            }
            $group->save();
            return ['status' => 1, 'text' => 'Сохранено', 'data' => ['group' => $group]];
        } else {
            return ['status' => 0, 'text' => 'Введите название'];
        }
    }

    public function update($id) {
        $group = UserGroup::find($id);
        if (request()->has('name')) {
            if (request()->input('name') != "") {
                $group->name = request()->input('name');
            } else {
                return ['status' => 0, 'text' => 'Введите название'];
            }
        }
        if (request()->has('icon')) {
            $group->icon = request()->input('icon');
        }
        if (request()->has('icon_svg_code')) {
            $group->icon_svg_code = request()->input('icon_svg_code');
        }
        $group->save();
        return ['status' => 1, 'text' => 'Сохранено'];
    }
}
