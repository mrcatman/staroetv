<?php

namespace App\Http\Controllers;

use App\Article;
use App\Channel;
use App\ChannelName;
use App\Comment;
use App\Genre;
use App\Helpers\CommentsHelper;
use App\Helpers\PermissionsHelper;
use App\HistoryEvent;
use App\Page;
use App\Program;
use App\Smile;
use App\User;
use App\UserAward;
use App\UserGroup;
use App\UserGroupConfig;
use App\UserReputation;
use App\Record;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

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


    public function getUsers() {
        $users = User::all();
        $groups = UserGroup::all();
        return view("pages.admin.users", [
            'groups' => $groups,
            'users' => $users
        ]);
    }

    public function changeUserGroup() {
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
        $user->save();
        return [
            'status' => 1,
            'text' => 'Сохранено'
        ];
    }

    public function changeUserPassword() {
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

    public function deleteUser() {
        $user = User::find(request()->input('user_id'));
        if (!$user) {
            return [
                'status' => 0,
                'text' => 'Пользователь не найден'
            ];
        }
        $user->delete();
        return [
            'status' => 1,
            'text' => 'Пользователь удален'
        ];
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

    public function getSmiles() {
        $smiles = Smile::with('picture')->get();
        return view("pages.admin.smiles", [
            'smiles' => $smiles
        ]);
    }

    public function saveSmiles() {
        $smiles = collect(request()->input('smiles'));
        $ids = $smiles->pluck('id')->toArray();
        foreach ($smiles as $smile) {
            $smile['show_in_panel'] = isset($smile['show_in_panel']) && $smile['show_in_panel'];
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

    public function getPages() {
        $static_pages = Page::all();
        return view("pages.admin.static_pages", [
            'static_pages' => $static_pages,
        ]);
    }

    public function getCategories() {
        $categories = Genre::all();
        return view("pages.admin.categories", [
            'categories' => $categories
        ]);
    }



    public function saveCategories() {
        $categories = collect(request()->input('categories'));
        $ids = $categories->pluck('id')->toArray();
        foreach ($categories as $category) {
            unset($category['created_at']);
            unset($category['updated_at']);
            if (isset($category['id'])) {
                $category_obj = Genre::find($category['id']);
                $category_obj->fill($category);
                $category_obj->save();
            } else {
                $category_obj = new Genre($category);
                $category_obj->save();
                $ids[] = $category_obj->id;
            }
        }
        Genre::whereNotIn('id', $ids)->delete();
        $all_categories = Genre::all();
        return [
            'status' => 1,
            'text' => 'Сохранено',
            'data' => [
                'categories' => $all_categories
            ]
        ];
    }

    public function getReputationHistory() {
        $reputation = UserReputation::orderBy('id', 'desc')->paginate(50);
        return view("pages.admin.reputation", [
            'reputation' => $reputation
        ]);
    }

    public function editorPanel() {
        if (PermissionsHelper::allows('redactorbar')) {
            $materials = [];

            if (PermissionsHelper::allows('viapprove')) {
                 $records = Record::where(['pending' => true])->orderBy('id', 'desc')->get();
                $materials['records'] = [
                    'name' => 'Записи',
                    'id' => 'records',
                    'items' => []
                ];
                foreach ($records as $record) {
                    $materials['records']['items'][] = [
                        'name' => $record->title,
                        'id' => $record->id,
                        'url' => $record->url
                    ];
                }
            }

            foreach ((new ArticlesController())->types_data as $type => $type_data) {
                if (PermissionsHelper::allows($type_data['permission_approve'])) {

                     $articles = Article::where(['type_id' => $type, 'pending' => true])->orderBy('id', 'desc')->limit(10)->get();

                    $materials[$type_data['base_link']] = [
                        'name' => $type_data['title'],
                        'id' => 'articles',
                        'items' => []
                    ];
                    foreach ($articles as $article) {
                        $materials[$type_data['base_link']]['items'][] = [
                            'name' => $article->title,
                            'id' => $article->id,
                            'url' => $article->url
                        ];
                    }
                }
            }

            if (PermissionsHelper::allows('contentapprove')) {
                 $channels = Channel::where(['pending' => true])->orderBy('id', 'desc')->get();
                $materials['channels'] = [
                    'name' => 'Каналы',
                    'id' => 'channels',
                    'items' => []
                ];
                foreach ($channels as $channel) {
                    $materials['channels']['items'][] = [
                        'name' => $channel->name,
                        'id' => $channel->id,
                        'url' => $channel->full_url
                    ];
                }

                $programs = Program::where(['pending' => true])->orderBy('id', 'desc')->get();
                $materials['programs'] = [
                    'name' => 'Программы',
                    'id' => 'programs',
                    'items' => []
                ];
                foreach ($programs as $program) {
                    $materials['programs']['items'][] = [
                        'name' => $program->name,
                        'id' => $program->id,
                        'url' => $program->full_url
                    ];
                }
            }

            if (PermissionsHelper::allows('historyapprove')) {
                $events = HistoryEvent::where(['pending' => true])->orderBy('id', 'desc')->get();
                $materials['events'] = [
                    'name' => 'Подборки',
                    'id' => 'events',
                    'items' => []
                ];
                foreach ($events as $event) {
                    $materials['events']['items'][] = [
                        'name' => $event->title,
                        'id' => $event->id,
                        'url' => $event->full_url
                    ];
                }
            }

            return view('pages.admin.editor_panel', [
                'materials' => $materials
            ]);
        } else {
            return view('pages.errors.403');
        }
    }

}
