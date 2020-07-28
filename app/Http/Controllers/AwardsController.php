<?php

namespace App\Http\Controllers;

use App\Award;
use App\Helpers\PermissionsHelper;
use App\User;
use App\UserAward;
use App\UserReputation;


class AwardsController extends Controller {

    public function ajax() {
        $user_id = request()->input('user_id');
        $user = User::find($user_id);
        if (!$user) {
            return [
                'status' => 0,
                'text' => 'Пользователь не существует'
            ];
        }
        $awards = $user->awards;
        return [
            'status' => 1,
            'data' => [
                'title' => 'Награды пользователя '.$user->username.' ('.count($user->awards).')',
                'html' => view("blocks/awards_modal_content", ['ajax' => true, 'awards' => $awards])->render()
            ]
        ];
    }

    public function list() {
        $awards = Award::all();
        $user_id = request()->input('user_id', '');
        return [
            'status' => 1,
            'data' => [
                'html' => view("blocks/awards_list", ['user_id' => $user_id, 'awards' => $awards])->render()
            ]
        ];
    }

    public function create() {
        if (PermissionsHelper::allows('awado')) {
            $award = Award::find(request()->input('award_id'));
            if (!$award) {
                return [
                    'status' => 0,
                    'text' => 'Награда не существует'
                ];
            }
            $user = User::find(request()->input('user_id'));
            if (!$user) {
                return [
                    'status' => 0,
                    'text' => 'Пользователь не найден'
                ];
            }
            $comment = request()->input('comment', '');
            $from_id = auth()->user()->id;
            $award_obj = new UserAward([
                'from_id' => $from_id,
                'to_id' => $user->id,
                'award_id' => $award->id,
                'comment' => $comment
            ]);
            $award_obj->save();
            return [
                'status' => 1,
                'text' => 'Награда добавлена',
                'dom' => [
                    [
                        'replace' => '.user-page__info-block__value--awards',
                        'html' => count($user->awards)
                    ]
                ]
            ];
        }
    }

    public function edit() {
        if (PermissionsHelper::allows('awado')) {
            $award = UserAward::find(request()->input('id'));
            if (!$award) {
                return [
                    'status' => 0,
                    'text' => 'Ошибка: объект не найден'
                ];
            }
            if (request()->has('comment')) {
                $award->comment = request()->input('comment');
            }
            $award->save();
            return [
                'status' => 1,
                'text' => 'Сохранено',
                'data' => [
                    'award' => $award
                ]
            ];
        } else {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }
    }

    public function delete() {
        if (PermissionsHelper::allows('awado')) {
            $award = UserAward::find(request()->input('id'));
            if (!$award) {
                return [
                    'status' => 0,
                    'text' => 'Ошибка: объект не найден'
                ];
            }
            $award->delete();
            return [
                'status' => 1,
                'text' => 'Удалено',
            ];
        } else {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }
    }


}
