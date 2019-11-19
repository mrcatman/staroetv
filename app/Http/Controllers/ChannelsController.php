<?php

namespace App\Http\Controllers;

use App\Channel;
use App\ChannelName;
use App\Helpers\PermissionsHelper;
use App\InterprogramPackage;
use App\Program;
use App\Record;
use Carbon\Carbon;

class ChannelsController extends Controller {

    public function show($url) {
        $channel = Channel::where(['url' => $url])->first();
        if (!$channel) {
            $channel = Channel::where(['id' => $url])->first();
        }
        if (!$channel) {
            return redirect("/");
        }
        $records = Record::where(['channel_id' => $channel->id])->orderBy('id', 'desc')->paginate(30);
        $records_count = Record::where(['channel_id' => $channel->id])->count();
        return view("pages.channels.show", [
            'channel' => $channel,
            'programs' => $channel->programs,
            'records' => $records,
            'records_count' => $records_count,
        ]);
    }

    public function add() {
        if (!PermissionsHelper::allows('channelsown') && !PermissionsHelper::allows('channels')) {
            return view("pages.errors.403");
        }
        $is_radio = !!request()->input('is_radio', false);
        return view("pages.forms.channel", [
            'channel' => null,
            'is_radio' => $is_radio,
        ]);
    }

    public function edit($id) {
        $channel = Channel::find($id);
        if (!$channel->can_edit) {
            return view("pages.errors.403");
        }
        $is_radio = $channel->is_radio;
        $all_channels = Channel::where(['is_radio' => $channel->is_radio])->get();
        return view("pages.forms.channel", [
            'channel' => $channel,
            'all_channels' => $all_channels,
            'is_radio' => $is_radio,
        ]);
    }

    public function save() {
        if (!PermissionsHelper::allows('channelsown') && !PermissionsHelper::allows('channels')) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }
        $channel = new Channel();
        return $this->fillData($channel);
    }

    public function update($id) {

        $channel = Channel::find($id);
        if (!$channel) {
            return [
                'status' => 0,
                'text' => 'Канал не найден'
            ];
        }
        if (!$channel->can_edit) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }
        return $this->fillData($channel);
    }

    private function fillData($channel) {
        $data = request()->validate([
            'name' => 'sometimes|min:1',
            'description' => 'sometimes',
            'logo_id' => 'sometimes',
            'is_regional' => 'sometimes',
            'is_federal' => 'sometimes',
            'is_abroad' => 'sometimes',
            'country' => 'sometimes',
            'city' => 'sometimes',
            'is_radio' => 'sometimes',
            'url' => 'sometimes'
        ]);

        foreach(['is_regional', 'is_abroad', 'is_federal'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = ($data[$key] === "true" || $data[$key] === true) ? 1 : 0;
            }
        }
        $channel->fill($data);
        $channel->save();
        if (request()->has('channel_names')) {
            $names = request()->input('channel_names');
            $names = json_decode($names);
            $ids = [];
            foreach ($names as $name) {
                $start = Carbon::parse($name->date_start);
                $end = Carbon::parse($name->date_end);

                $name_data = [
                    'channel_id' => $channel->id,
                    'name' => $name->name,
                    'logo_id' => $name->logo_id,
                    'date_start' => !$start->isToday() ? $start  : null,
                    'date_end' => !$end->isToday() ? $end : null
                ];
                if (!isset($name->id)) {
                    $name = new ChannelName($name_data);
                    $name->save();
                    $ids[] = $name->id;
                } else {
                    $ids[] = $name->id;
                    $name = ChannelName::find($name->id);
                    $name->fill($name_data);
                    $name->save();
                }
            }
            ChannelName::where(['channel_id' => $channel->id])->whereNotIn('id', $ids)->delete();
        }
        return [
            'status' => 1,
            'text' => 'Информация о канале обновлена',
            'redirect_to' => '/channels/'.$channel->id.'/edit'
        ];
    }

    public function merge() {
        $original = Channel::find(request()->input('original_id'));
        if (!$original) {
            return [
                'status' => 0,
                'text' => 'Канал не найден'
            ];
        }
        if (!$original->can_edit) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }

        if (request()->input('is_advertising') === 'true') {
            Record::where(['channel_id' => $original->id])->update(['is_advertising' => true]);
        } else {
            $merged = Channel::find(request()->input('merged_id'));
            if (!$merged) {
                return [
                    'status' => 0,
                    'text' => 'Канал для объединения не найден'
                ];
            }
            Record::where(['channel_id' => $original->id])->update(['channel_id' => $merged->id]);
            Program::where(['channel_id' => $original->id])->update(['channel_id' => $merged->id]);
            ChannelName::where(['channel_id' => $original->id])->update(['channel_id' => $merged->id]);
        }
        $original->delete();
        return [
            'status' => 1,
            'text' => 'Канал объединен',
            'redirect_to' => '/videos'
        ];
    }

    public function getAjaxList($is_radio = false) {
        $channels = Channel::where(['is_radio' => $is_radio])->with(['names', 'logo'])->get();
        return [
            'status' => 1,
            'data' => [
                'channels' => $channels
            ]
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

    public function delete() {
        $channel = Channel::find(request()->input('channel_id'));
        if (!$channel) {
            return [
                'status' => 0,
                'text' => 'Канал не найден'
            ];
        }
        if (!$channel->can_edit) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }
        $channel->delete();
        return [
            'status' => 1,
            'text' => 'Канал удален'
        ];
    }
}
