<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RecordComplaint;

class RecordComplaintsController extends Controller {

    public function index() {
        $complaints = RecordComplaint::orderBy('id', 'desc');
        $complaints = $complaints->paginate(24);
        return view("pages.admin.records-complaints", [
            'complaints' => $complaints,
        ]);
    }


}
