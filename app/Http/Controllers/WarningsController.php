<?php

namespace App\Http\Controllers;

use App\Channel;
use App\ChannelName;
use App\Comment;
use App\Helpers\CommentsHelper;
use App\Program;
use App\UserReputation;
use App\UserWarning;
use App\Record;
use Carbon\Carbon;

class WarningsController extends Controller {

    public function ajax() {
        $user_id = request()->input('user_id');
        $warnings = UserWarning::where(['to_id' => $user_id])->orderBy('id', 'desc')->get();
        return [
            'status' => 1,
            'data' => [
                'html' => view("blocks/warnings_modal_content", ['ajax' => true, 'warnings' => $warnings])->render()
            ]
        ];
    }


}
