<?php

namespace App\Helpers;

use App\Constants\Actions;
use App\Constants\MaterialTypes;
use App\Models\ActionsLog;
use Illuminate\Database\Eloquent\Model;

class ActionsLogHelper {

    public static function create(Model $material, int $action, $additional_changes = []) {

        //todo merge action
        $user = auth()->user();
        $material_type = array_flip(MaterialTypes::LIST)[$material::class];

        $log = new ActionsLog([
            'user_id' => $user->id,
            'action' => $action,
            'material_id' => $material->id,
            'material_type' => $material_type,
        ]);

        $changes = $additional_changes;

        if ($action == Actions::Create || $action == Actions::Update) {
            foreach ($material->getDirty() as $key => $value) {
                if ($action == Actions::Update) {
                    $key_changes = [$material->getOriginal($key), $value];
                    if (self::valueChanged($key_changes[0], $key_changes[1])) {
                        $changes[$key] = $key_changes;
                    }
                } else {
                    $changes[$key] = [$value];
                }
            }
        }

        if (count(array_keys($changes)) > 0) {
            $log->changes = $changes;
        } else {
            if ($action == Actions::Update) {
                return; // игнорируем, если ничего не обновлено
            }
        }

        if ($action == Actions::Create || $action == Actions::Update) {
            $material->save();
            $log->material_id = $material->id;
        } elseif ($action == Actions::Delete) {
            $material->delete();
        }

        $log->save();
    }

    private static function valueChanged($old, $new)
    {
        if ($old === 0 && $new === false || $old === false && $new === 0) {
            return false;
        }

        if ($old === 1 && $new === true || $old === true && $new === 1) {
            return false;
        }

        return true;
    }
}
