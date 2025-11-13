<?php

namespace App\Helpers;

use App\Constants\Actions;
use App\Constants\MaterialTypes;
use App\Models\ActionsLog;
use Illuminate\Database\Eloquent\Model;

class ActionsLogHelper {

    public static function create(Model $material, int $action) {

        //todo merge action
        $user = auth()->user();
        $material_type = array_flip(MaterialTypes::LIST)[$material::class];

        $log = new ActionsLog([
            'user_id' => $user->id,
            'action' => $action,
            'material_id' => $material->id,
            'material_type' => $material_type,
        ]);

        if ($action == Actions::Create || $action == Actions::Update) {
            $log->changes = $material->getChanges();
        }

        $log->save();
    }
}
