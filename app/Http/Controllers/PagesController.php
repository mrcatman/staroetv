<?php

namespace App\Http\Controllers;

use App\Channel;
use App\Helpers\PermissionsHelper;
use App\Helpers\ViewsHelper;
use App\Page;
use App\Program;
use App\User;

class PagesController extends Controller {

    public function show($id) {
        $page = Page::find($id);
        if (!$page) {
            return redirect('https://staroetv.su/');
        }

        if (PermissionsHelper::checkGroupAccess("can_read", $page)) {
            ViewsHelper::increment($page,'pages');
            return view("pages.static", [
                'page' => $page,
            ]);
        } else {
            return view("pages.errors.403");
        }
    }

    public function showByURL($url) {
        $page = Page::where(['url' => $url])->first();
        if (!$page) {
            return redirect('https://staroetv.su/');
        }
        if (PermissionsHelper::checkGroupAccess("can_read", $page)) {
            ViewsHelper::increment($page,'pages');
            return view("pages.static", [
                'page' => $page,
            ]);
        } else {
            return view("pages.errors.403");
        }
    }


    public function add() {
        if (!PermissionsHelper::allows('sipadd')) {
            return redirect('https://staroetv.su/');
        }
        return view("pages.forms.static", [
            'page' => null,
        ]);
    }

    public function edit($id) {
        if (!PermissionsHelper::allows('sipedt')) {
            return redirect('https://staroetv.su/');
        }
        $page = Page::where(['id' => $id])->first();
        return view("pages.forms.static", [
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
        $page->save();
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
        $page->save();
        return [
            'status' => 1,
            'text' => 'Сохранено',
            'redirect_to' => $page->full_url
        ];
    }


    public function delete() {
        if (!PermissionsHelper::allows('sipdel')) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }
        $page = Page::find(request()->input('page_id'));
        if (!$page) {
            return [
                'status' => 0,
                'text' => 'Страница не найдена'
            ];
        }
        $page->delete();
        return [
            'status' => 1,
            'text' => 'Удалено',
            'redirect_to' => "/"
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
                    return view('blocks.group_users_list', ['users' => $users]);
                },
                $page->content
            );
            return view("pages.static", [
                'page' => $page,
            ]);
        } else {
            return view("pages.errors.403");
        }
    }
}
