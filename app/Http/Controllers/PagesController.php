<?php

namespace App\Http\Controllers;

use App\Channel;
use App\Page;
use App\Program;

class PagesController extends Controller {

    public function show($id) {
        $page = Page::find($id);
        if (!$page) {
            return redirect('/');
        }
        return view("pages.static", [
            'page' => $page,
        ]);
    }

}
