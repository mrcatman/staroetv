<?php

namespace App\Http\Controllers;

use App\Channel;
use App\ChannelName;
use App\Comment;
use App\ForumMessage;
use App\Helpers\CommentsHelper;
use App\Helpers\PermissionsHelper;
use App\Program;
use App\User;
use App\UserReputation;
use App\Record;
use Carbon\Carbon;

class ReputationController extends Controller {

    protected $reputation_timeout = 60 * 60 * 24;
    protected $reputation_change_level = 50;

    public function ajax() {
        $user_id = request()->input('user_id');
        $reputation = UserReputation::where(['to_id' => $user_id])->orderBy('id', 'desc')->get();
        return [
            'status' => 1,
            'data' => [
                'html' => view("blocks/reputation_modal_content", ['ajax' => true, 'reputation' => $reputation])->render()
            ]
        ];
    }

    public function change() {
        $user = User::find(request()->input('user_id'));
        if (!$user) {
            return [
                'status' => 0,
                'text' => 'Пользователь не существует'
            ];
        }
        if (!$user->can_change_reputation) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }
        $last_change = UserReputation::where(['from_id' => auth()->user()->id, 'to_id' => $user->id])->orderBy('id', 'desc')->first();
        if ($last_change) {
            if (time() - $last_change->created_at->timestamp < $this->reputation_timeout && !PermissionsHelper::allows("dorep1")) {
                return [
                    'status' => 0,
                    'text' => 'Вы уже недавно меняли репутацию этому пользователю'
                ];
            }
        }
        $own_reputation = auth()->user()->reputation_number;
        $number = ceil($own_reputation / $this->reputation_change_level);
        $action = (int)request()->input('action');
        $reputation_obj = new UserReputation([
            'from_id' => auth()->user()->id,
            'to_id' => $user->id,
        ]);
        $reputation_number = $user->reputation_number;
        if (request()->has('comment')) {
            $reputation_obj->comment = request()->input('comment');
        }

        if ($action == 1) {
            $reputation_obj->weight = $number;
        } elseif ($action == -1) {
            $reputation_obj->weight = -1 * $number;
        } else {
            $reputation_obj->weight = 0;
        }
        if (request()->has('forum_message_id')) {
            $message = ForumMessage::find(request()->input('forum_message_id'));
            if ($message) {
                $message_id = $message->id;
                $created_at = $message->created_at_ts;
                $topic_id = $message->topic_id;
                $forum_id = $message->topic->forum_id;
                $link = "/forum/$forum_id-$topic_id-$message_id-$created_at";
                $reputation_obj->link = $link;
            }
        }
        $reputation_obj->save();
        $reputation_number += $reputation_obj->weight;
        return [
            'status' => 1,
            'text' => 'Сохранено',
            'data' => [
                'dom' => [
                    [
                        'replace' => ".user-page__info-block__value--reputation, .forum-message__reputation__number[data-user-id=".$user->id."]",
                        'html' => $reputation_number
                    ]
                ]
            ]

        ];
    }


}
