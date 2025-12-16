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

class GenresController extends Controller {

    public function index() {
        $categories = Genre::all();
        return view("pages.admin.categories", [
            'categories' => $categories
        ]);
    }

    public function save() {
        $categories = collect(request()->input('categories'));
        $ids = $categories->pluck('id')->toArray();
        foreach ($categories as $category) {
            unset($category['created_at']);
            unset($category['updated_at']);
            if (isset($category['id'])) {
                $category_obj = Genre::find($category['id']);
                $category_obj->fill($category);

                ActionsLogHelper::create($category_obj, Actions::Update);
            } else {
                $category_obj = new Genre($category);

                ActionsLogHelper::create($category_obj, Actions::Create);

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

}
