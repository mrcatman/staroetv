<?php

namespace App\Http\Controllers;

use App\Channel;
use App\Program;

class ProgramsController extends Controller {

    public function show($id) {
        $program = Program::find($id);
        return view("pages.program", [
            'program' => $program,
            'videos' => $program->videos,
        ]);
    }
}
