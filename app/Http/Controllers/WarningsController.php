<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionsHelper;
use App\User;
use App\UserWarning;

class WarningsController extends Controller {

    public function ajax() {
        $user_id = request()->input('user_id');
        $user = User::find($user_id);
        if (!$user) {
            return [
                'status' => 0,
                'text' => 'Пользователь не существует'
            ];
        }
        $warnings = $user->warnings;
        $level = $warnings->sum('weight');
        return [
            'status' => 1,
            'data' => [
                'title' => 'Уровень замечаний пользователя '.$user->username. ': '.$level.' ( '.$user->ban_level.'%)',
                'html' => view("blocks/warnings_modal_content", ['ajax' => true, 'warnings' => $warnings])->render()
            ]
        ];
    }


    public function form() {
        $user_id = request()->input('user_id', '');
        return [
            'status' => 1,
            'data' => [
                'html' => view("blocks/warnings_form", ['user_id' => $user_id])->render()
            ]
        ];
    }

    public function add() {
        if (PermissionsHelper::allows('doban')) {
            $data = request()->validate([
                'comment' => 'required|min:1',
                'count' => 'required|numeric',
                'units' => 'required|in:days,hours',
                'user_id' => 'required|numeric|min:1',
                'weight' => 'required|in:1,-1',
                'forever' => 'boolean'
            ]);
            $user = User::find($data['user_id']);
            if (!$user) {
                return [
                    'status' => 0,
                    'text' => 'Пользователь не найден'
                ];
            }
            if ($data['weight'] == -1) {
                $data['weight'] = 0;
            }
            $ban = new UserWarning();
            $ban->to_id = $user->id;
            $ban->weight = $data['weight'] == 1 ? 1 : 0;
            $ban->from_id = auth()->user()->id;
            $ban->comment = $data['comment'];
            if ($ban->weight == 1) {
                if ($data['units'] == "days") {
                    $time = time() + (86400 * $data['count']);
                } else {
                    $time = time() + (3600 * $data['count']);
                }
                $ban->time_expires = $time;
            }
            $is_forever = (bool)$data['forever'];
            if ($is_forever) {
                $user->group_id = 255;
                $user->save();
            }
            $ban->save();
            return [
                'status' => 1,
                'text' => 'Замечание добавлено'
            ];
        } else {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }
    }

}
