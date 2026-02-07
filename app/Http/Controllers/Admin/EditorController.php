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

class EditorController extends Controller
{


    public function approvePanel()
    {
        if (!PermissionsHelper::allows('redactorbar')) {
            return redirect('/');
        }
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
                    'url' => $record->url,
                    'user' => $record->user,
                    'created_at' => $record->created_at,
                ];
            }
        }


        if (PermissionsHelper::allows('nwapprove')) {

            $articles = Article::where(['pending' => true])->orderBy('id', 'desc')->limit(10)->get();

            $materials['articles'] = [
                'name' => 'Новости и статьи',
                'id' => 'articles',
                'items' => []
            ];
            foreach ($articles as $article) {
                $materials['articles']['items'][] = [
                    'name' => $article->title,
                    'id' => $article->id,
                    'url' => $article->full_url,
                    'user' => $article->user,
                    'created_at' => $article->created_at,
                ];
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
                    'url' => $channel->full_url,
                    'user' => $channel->user,
                    'created_at' => $channel->created_at,
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
                    'url' => $program->full_url,
                    'user' => $program->user,
                    'created_at' => $program->created_at,
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
                    'url' => $event->full_url,
                    'user' => $event->user,
                    'created_at' => $event->created_at,
                ];
            }
        }

        return view('pages.redactor.approve-panel', [
            'materials' => $materials
        ]);

    }

    public function commercialsPanel() {
        if (!PermissionsHelper::allows('viedit')) {
            return redirect('/');
        }
        return view('pages.redactor.commercials-panel');
    }

    public function getRandomCommercial() {
        if (!PermissionsHelper::allows('viedit')) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }
        $record = Record::where(['is_advertising' => true])->whereNull('advertising_category')->inRandomOrder()->first();
        $record->append(['source_hls', 'source_telegram']);
        return [
            'status' => 1,
            'data' => [
                'record' => $record,
            ]
        ];
    }

}
