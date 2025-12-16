<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\PermissionsHelper;
use App\Http\Controllers\ArticlesController;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Channel;
use App\Models\HistoryEvent;
use App\Models\Program;
use App\Models\Record;

class EditorController extends Controller {


    public function panel() {
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

                    $materials[$type_data['base_url']] = [
                        'name' => $type_data['title'],
                        'id' => 'articles',
                        'items' => []
                    ];
                    foreach ($articles as $article) {
                        $materials[$type_data['base_url']]['items'][] = [
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
