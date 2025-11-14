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

class AdminController extends Controller {












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



    public function getPages() {
        $static_pages = Page::all();
        return view("pages.admin.static_pages", [
            'static_pages' => $static_pages,
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
