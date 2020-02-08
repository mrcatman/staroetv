<?php

namespace App\Http\Controllers;

use App\Channel;
use App\Helpers\PermissionsHelper;
use App\Program;
use App\Questionnaire;
use App\QuestionnaireAnswer;
use App\QuestionnaireVariant;

class QuestionnairesController extends Controller {

    public function vote() {
        if ((!$user = auth()->user()) || !PermissionsHelper::allows('frdopoll')) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }
        $questionnaire = Questionnaire::find(request()->input('questionnaire_id'));
        if (!$questionnaire) {
            return [
                'status' => 0,
                'text' => 'Опрос не найден'
            ];
        }
        QuestionnaireAnswer::where(['user_id' => $user->id, 'questionnaire_id' => $questionnaire->id])->delete();
        if (!request()->has('variant')) {
            return [
                'status' => 0,
                'text' => $questionnaire->multiple_variants ? 'Выберите хотя бы 1 вариант' : 'Выберите вариант'
            ];
        }
        $user_variant = request()->input('variant');
        if (is_array($user_variant) && count($user_variant) == 0) {
            return [
                'status' => 0,
                'text' => $questionnaire->multiple_variants ? 'Выберите хотя бы 1 вариант' : 'Выберите вариант'
            ];
        }
        if (is_array($user_variant) && !$questionnaire->multiple_variants) {
            $user_variant = $user_variant[0];
        } elseif (!is_array($user_variant) && $questionnaire->multiple_variants) {
            $user_variant = [$user_variant];
        }
        if ($questionnaire->multiple_variants) {
            foreach ($user_variant as $variant_id) {
                $answer = new QuestionnaireAnswer([
                    'user_id' => $user->id,
                    'questionnaire_id' => $questionnaire->id,
                    'variant_id' => $variant_id
                ]);
                $answer->save();
            }
        } else {
            $answer = new QuestionnaireAnswer([
                'user_id' => $user->id,
                'questionnaire_id' => $questionnaire->id,
                'variant_id' => $user_variant
            ]);
            $answer->save();
        }
        return [
            'status' => 1,
            'text' => 'Ваш голос принят',
            'data' => [
                'dom' => [
                    [
                        'replace' => ".questionnaire__container",
                        'html' => view('blocks.questionnaire', ['questionnaire' => $questionnaire, 'show_results' => true])->render()
                    ]
                ]
            ]
        ];
    }

    public function form() {
        $questionnaire = Questionnaire::find(request()->input('questionnaire_id'));
        if (!$questionnaire) {
            return [
                'status' => 0,
                'text' => 'Опрос не найден'
            ];
        }
        $show_results = (bool)request()->input('show_results', false);
        return [
            'status' => 1,
            'data' => [
                'dom' => [
                    [
                        'replace' => ".questionnaire__container",
                        'html' => view('blocks.questionnaire', ['questionnaire' => $questionnaire, 'show_results' => $show_results])->render()
                    ]
                ]
            ]
        ];
    }

    public function save($topic_id) {
        $data = json_decode(request()->input('questionnaire_data'));
        $questionnaire = Questionnaire::firstOrNew(['topic_id' => $topic_id]);
        if (!isset($data->title) || $data->title == "") {
            //throw new \Exception("Введите название опроса");
        }
        $questionnaire->title = $data->title;
        $questionnaire->multiple_variants = isset($data->multiple_variants) ? (bool)$data->multiple_variants : false;
        if (!isset($data->variants) || count($data->variants) === 0) {
            throw new \Exception("Введите хотя бы 1 вариант для опроса");
        }
        $i = 0;
        $questionnaire->save();
        $old_variant_ids = $questionnaire->variants->pluck('id')->toArray();
        $new_variant_ids = [];
        foreach ($data->variants as $variant) {
            $variant_obj = null;
            if (isset($variant->id)) {
                $variant_obj = QuestionnaireVariant::find($variant->id);
                if ($variant_obj && $variant_obj->questionnaire_id == $questionnaire->id) {
                    $variant_obj->order = $i;
                    $variant_obj->title = $variant->title;
                    $variant_obj->save();
                    $new_variant_ids[] = $variant_obj->id;
                }
            }
            if (!$variant_obj) {
                $variant_obj = new QuestionnaireVariant([
                    'questionnaire_id' => $questionnaire->id,
                    'order' => $i,
                    'title' => $variant->title,
                    'initial_count' => 0
                ]);
                $variant_obj->save();
                $new_variant_ids[] = $variant_obj->id;
            }
            $i++;
        }
        $variants_to_delete = array_diff($old_variant_ids, $new_variant_ids);
        QuestionnaireVariant::whereIn('id', $variants_to_delete)->delete();
        return true;
    }
}
