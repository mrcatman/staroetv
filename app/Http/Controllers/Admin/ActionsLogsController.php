<?php

namespace App\Http\Controllers\Admin;

use App\Constants\MaterialTypes;
use App\Http\Controllers\Controller;
use App\Models\ActionsLog;

class ActionsLogsController extends Controller {

    private $material_types = [
        MaterialTypes::TYPE_CHANNELS => 'Канал',
        MaterialTypes::TYPE_ARTICLES => 'Статья',
        MaterialTypes::TYPE_NEWS => 'Новость',
        MaterialTypes::TYPE_BLOG => 'Блог',
        MaterialTypes::TYPE_RECORDS => 'Запись',
        MaterialTypes::TYPE_TELETEXT => 'Телетекст',
        MaterialTypes::TYPE_PROGRAMS => 'Программа',
        MaterialTypes::TYPE_INTERPROGRAM => 'Пакет оформления',
        MaterialTypes::TYPE_HISTORY_EVENTS => 'Подборка записей',
        MaterialTypes::TYPE_USERS => 'Пользователь',
        MaterialTypes::TYPE_AWARDS => 'Награда',
        MaterialTypes::TYPE_REPUTATION => 'Изменение в репутации',
        MaterialTypes::TYPE_WARNINGS => 'Замечание',
        MaterialTypes::TYPE_COMMENTS => 'Коммент',
        MaterialTypes::TYPE_SMILES => 'Смайл',
        MaterialTypes::TYPE_PAGES => 'Страница',
        MaterialTypes::TYPE_FORUM_TOPICS => 'Тема на форуме',
        MaterialTypes::TYPE_FORUMS => 'Форум',
        MaterialTypes::TYPE_FORUM_MESSAGES => 'Сообщение на форуме',
    ];

    public function index() {
        $logs = ActionsLog::orderBy('id', 'desc');
        if (request()->input('material_type') != '') {
            $logs = $logs->where('material_type', request()->input('material_type'));
        }
        if (request()->input('material_id') != '') {
            $logs = $logs->where('material_id', request()->input('material_id'));
        }
        $logs = $logs->paginate(24);
        return view("pages.admin.actions-logs", [
            'logs' => $logs,
            'material_types' => $this->material_types,
        ]);
    }


}
