<?php

namespace App\Http\Controllers;

use App\Channel;
use App\ChannelName;
use App\Comment;
use App\Helpers\CommentsHelper;
use App\Program;
use App\UserAward;
use App\UserReputation;
use App\Video;
use Carbon\Carbon;

class AwardsController extends Controller {

    public function ajax() {
        $user_id = request()->input('user_id');
        $awards = UserAward::where(['to_id' => $user_id])->orderBy('id', 'desc')->get();
        return [
            'status' => 1,
            'data' => [
                'html' => view("blocks/awards_modal_content", ['ajax' => true, 'awards' => $awards])->render()
            ]
        ];
    }


}
