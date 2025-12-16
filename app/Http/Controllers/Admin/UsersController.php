<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Actions;
use App\Constants\Permissions;
use App\Helpers\ActionsLogHelper;
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

class UsersController extends Controller {

    public function index() {
        $users = User::paginate(24);
        $groups = UserGroup::all();
        return view("pages.admin.users", [
            'groups' => $groups,
            'users' => $users
        ]);
    }

    public function changeGroup() {
        if (!PermissionsHelper::allows('usrepl')) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }
        $user = User::find(request()->input('user_id'));
        if (!$user) {
            return [
                'status' => 0,
                'text' => 'Пользователь не найден'
            ];
        }
        $group = UserGroup::find(request()->input('group_id', 1));
        if (!$group) {
            return [
                'status' => 0,
                'text' => 'Группа не найдена'
            ];
        }
        if ($user->id === auth()->user()->id) {
            return [
                'status' => 0,
                'text' => 'Вы не можете снять с себя админку'
            ];
        }
        $user->group_id = request()->input('group_id', $group->id);
        ActionsLogHelper::create($user, Actions::Update);

        return [
            'status' => 1,
            'text' => 'Сохранено'
        ];
    }

    public function changePassword() {
        $user = User::find(request()->input('user_id'));
        if (!$user) {
            return [
                'status' => 0,
                'text' => 'Пользователь не найден'
            ];
        }
        if (!request()->has('new_password') || request()->input('new_password') == "") {
            return [
                'status' => 0,
                'text' => 'Введите пароль'
            ];
        }
        $password = request()->has('new_password');
        $user->password = Hash::make($password);
        $user->save();
        return [
            'status' => 1,
            'text' => 'Сохранено'
        ];
    }

    public function delete() {
        $user = User::find(request()->input('user_id'));
        if (!$user) {
            return [
                'status' => 0,
                'text' => 'Пользователь не найден'
            ];
        }

        ActionsLogHelper::create($user, Actions::Delete);
        $user->delete();

        return [
            'status' => 1,
            'text' => 'Пользователь удален'
        ];
    }

    public function getReputationHistory() {
        $reputation = UserReputation::orderBy('id', 'desc')->paginate(50);
        return view("pages.admin.reputation", [
            'reputation' => $reputation
        ]);
    }

}
