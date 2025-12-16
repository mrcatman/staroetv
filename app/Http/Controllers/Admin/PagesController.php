<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;

class PagesController extends Controller {

    public function index() {
        $static_pages = Page::all();
        return view("pages.admin.static", [
            'static_pages' => $static_pages,
        ]);
    }


}
