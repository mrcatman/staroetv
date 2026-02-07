<?php

namespace App\Http\Controllers;

use App\Constants\Actions;
use App\Helpers\ActionsLogHelper;
use App\Helpers\PermissionsHelper;
use App\Helpers\ViewsHelper;
use App\Models\Page;
use App\Models\User;

class PagesController extends EntityController {

    protected $entity_class = Page::class;
    protected $permissions = [
        'approve' => '',
        'delete' => 'sipdel'
    ];

    public function show($id) {
        $page = Page::find($id);
        if (!$page) {
            return redirect(route('index'));
        }

        if (PermissionsHelper::checkGroupAccess("can_read", $page)) {
            ViewsHelper::increment($page,'pages');
            return view("pages.static.show", [
                'page' => $page,
            ]);
        } else {
            return redirect('/');
        }
    }

    public function showByURL($url) {
        $page = Page::where(['url' => $url])->first();
        if (!$page) {
            return redirect(route('index'));
        }
        if (PermissionsHelper::checkGroupAccess("can_read", $page)) {
            ViewsHelper::increment($page,'pages');
            return view("pages.static.show", [
                'page' => $page,
            ]);
        } else {
            return redirect('/');
        }
    }


    public function add() {
        if (!PermissionsHelper::allows('sipadd')) {
            return redirect(route('index'));
        }
        return view("pages.static.form", [
            'page' => null,
        ]);
    }

    public function edit($id) {
        if (!PermissionsHelper::allows('sipedt')) {
            return redirect(route('index'));
        }
        $page = Page::where(['id' => $id])->first();
        return view("pages.static.form", [
            'page' => $page,
        ]);
    }

    public function update($id) {
        $data = request()->validate([
            'title' => 'required|min:1',
            'content' => 'required|min:1',
            'url' => 'sometimes',
            'can_read' => 'sometimes'
        ]);
        if (!PermissionsHelper::allows('sipedt')) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }
        $page = Page::find($id);
        if (!$page) {
            return [
                'status' => 0,
                'text' => 'Страница не найдена'
            ];
        }
        $page->fill($data);
        $page->last_updated_by = auth()->user()->username;

        ActionsLogHelper::create($page, Actions::Update);

        return [
            'status' => 1,
            'text' => 'Сохранено'
        ];
    }

    public function save() {
        $data = request()->validate([
            'title' => 'required|min:1',
            'content' => 'required|min:1',
            'url' => 'sometimes',
            'can_read' => 'sometimes'
        ]);
        if (!PermissionsHelper::allows('sipadd')) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }
        $page = new Page($data);
        $page->last_updated_by = auth()->user()->username;

        ActionsLogHelper::create($page, Actions::Create);

        return [
            'status' => 1,
            'text' => 'Сохранено',
            'redirect_to' => $page->full_url
        ];
    }

    public function team() {
        $page = Page::find(128);
        if (PermissionsHelper::checkGroupAccess("can_read", $page)) {
            ViewsHelper::increment($page,'pages');
            $page->content = preg_replace_callback(
                '/team\|\d+/',
                function ($matches) {
                    $group_id = explode("|", $matches[0])[1];
                    $users = User::where(['group_id' => $group_id])->get();
                    return view('blocks.global.group-users-list', ['users' => $users]);
                },
                $page->content
            );
            return view("pages.static.show", [
                'page' => $page,
            ]);
        } else {
            return redirect('/');
        }
    }
}
