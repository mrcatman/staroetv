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

class SmilesController extends Controller {


    public function index() {
        $smiles = Smile::with('picture')->get();
        return view("pages.admin.smiles", [
            'smiles' => $smiles
        ]);
    }

    public function save() {
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

}
