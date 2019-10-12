<?php

namespace App\Http\Controllers;

use App\Channel;
use App\Program;

class ProgramsController extends Controller {

    public function show($id) {
        $program = Program::find($id);
        return view("pages.programs.show", [
            'program' => $program,
            'records' => $program->records,
        ]);
    }
}
