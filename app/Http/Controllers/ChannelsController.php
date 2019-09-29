<?php

namespace App\Http\Controllers;

use App\Channel;
use App\ChannelName;
use App\Helpers\PermissionsHelper;
use App\InterprogramPackage;
use App\Program;
use App\Video;
use Carbon\Carbon;

class ChannelsController extends Controller {

    public function show($id) {
        $channel = Channel::find($id);
        return view("pages.channel", [
            'channel' => $channel,
            'programs' => $channel->programs,
            'videos' => $channel->videos,
        ]);
    }

    public function edit($id) {
        if (!PermissionsHelper::allows('viadd')) {
            return view("pages.errors.403");
        }
        $channel = Channel::find($id);
        $all_channels = Channel::all();
        return view("pages.forms.channel", [
            'channel' => $channel,
            'all_channels' => $all_channels,
        ]);
    }

    public function update($id) {
        if (!PermissionsHelper::allows('viadd')) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }
        $data = request()->validate([
            'name' => 'required|min:1',
            'description' => 'sometimes',
            'logo_id' => 'sometimes',
        ]);
        if (request()->has('channel_names')) {
           $names = request()->input('channel_names');
           $names = json_decode($names);
           foreach ($names as $name) {
               $start = Carbon::parse($name->date_start);
               $end = Carbon::parse($name->date_end);

               $name_data = [
                   'channel_id' => $id,
                   'name' => $name->name,
                   'logo_id' => $name->logo_id,
                   'date_start' => !$start->isToday() ? $start  : null,
                   'date_end' => !$end->isToday() ? $end : null
               ];
               if (!isset($name->id)) {
                   $name = new ChannelName($name_data);
                   $name->save();
               } else {
                   $name = ChannelName::find($name->id);
                   $name->fill($name_data);
                   $name->save();
               }
           }
        }
        Channel::where(['id' => $id])->update($data);
        return [
            'status' => 1,
            'text' => 'Информация о канале обновлена',
            'redirect_to' => '/channels/'.$id.'/edit'
        ];
    }

    public function merge() {
        $original = Channel::find(request()->input('original_id'));
        $merged = Channel::find(request()->input('merged_id'));
        Video::where(['channel_id' => $original->id])->update(['channel_id' => $merged->id]);
        Program::where(['channel_id' => $original->id])->update(['channel_id' => $merged->id]);
        $original->delete();
        return [
            'text' => 'Канал объединен',
            'redirect_to' => '/videos'
        ];
    }

    public function getPrograms($id) {
        $channel = Channel::find($id);
        if (!$channel) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }
        return [
            'status' => 1,
            'data' => [
                'programs' => Program::where(['channel_id' => $channel->id])->with('coverPicture')->get()
            ]
        ];
    }

    public function getInterprogramPackages($id) {
        $channel = Channel::find($id);
        if (!$channel) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }
        return [
            'status' => 1,
            'data' => [
                'interprogram_packages' => InterprogramPackage::where(['channel_id' => $channel->id])->get()
            ]
        ];
    }
}
