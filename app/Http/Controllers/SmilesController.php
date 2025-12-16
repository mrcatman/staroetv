<?php

namespace App\Http\Controllers;

use App\Models\Smile;

class SmilesController extends Controller {

    public function ajax() {
        $smiles = Smile::all();
        return [
            'status' => 1,
            'data' => [
                'title' => 'Все смайлы',
                'html' => view('blocks.bb-editor.smiles', ['smiles' => $smiles])->render()
            ]
        ];
    }


}
