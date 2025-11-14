<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Permissions;
use App\Helpers\PermissionsHelper;
use App\Http\Controllers\ArticlesController;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Channel;
use App\Models\Genre;
use App\Models\HistoryEvent;
use App\Models\Page;
use App\Models\Program;
use App\Models\Record;
use App\Models\Smile;
use App\Models\User;
use App\Models\UserGroup;
use App\Models\UserGroupConfig;
use App\Models\UserReputation;
use Illuminate\Support\Facades\Hash;

class ChannelsController extends Controller {

    public function index() {
        $channels = Channel::with('logo')->get();
        return view("pages.admin.channels", [
            'channels' => $channels
        ]);
    }

    public function getOrder() {
        $channels = Channel::with('logo')->orderBy('order', 'ASC')->get();
        return view("pages.admin.channels_order", [
            'channels' => $channels
        ]);
    }

    public function saveOrder() {
        foreach (request()->input('order') as $channel_id => $order) {
            Channel::where(['id' => $channel_id])->update(['order' => $order]);

        }
        return [
            'status' => 1,
            'text' => 'Сохранено',
        ];
    }

}
