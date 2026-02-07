<?php

namespace App\Http\Controllers;

use App\Constants\Actions;
use App\Helpers\ActionsLogHelper;
use App\Helpers\PermissionsHelper;
use App\Models\Award;
use App\Models\User;
use App\Models\UserAward;


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
                'html' => view("blocks.awards.modal-content", ['ajax' => true, 'awards' => $awards])->render()
            ]
        ];
    }

    public function form() {
        $awards = Award::all();
        $user_id = request()->input('user_id', '');
        return [
            'status' => 1,
            'data' => [
                'html' => view("blocks.awards.list", ['user_id' => $user_id, 'awards' => $awards])->render()
            ]
        ];
    }

    public function create() {
        if (PermissionsHelper::allows('awado')) {
            $award = Award::find(request()->input('award_id'));
            if (!$award) {
                return [
                    'status' => 0,
                    'text' => 'Ошибка: награда не существует'
                ];
            }
            $user = User::find(request()->input('user_id'));
            if (!$user) {
                return [
                    'status' => 0,
                    'text' => 'Ошибка: пользователь не найден'
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

            ActionsLogHelper::create($award_obj, Actions::Create);

            return [
                'status' => 1,
                'text' => 'Награда добавлена',
                'html' => [
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
                    'text' => 'Ошибка: награда не найдена'
                ];
            }
            if (request()->has('comment')) {
                $award->comment = request()->input('comment');
            }

            ActionsLogHelper::create($award, Actions::Update);

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
                    'text' => 'Ошибка: награда не найдена'
                ];
            }

            ActionsLogHelper::create($award, Actions::Delete);

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
