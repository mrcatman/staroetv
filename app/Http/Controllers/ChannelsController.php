<?php

namespace App\Http\Controllers;

use App\AdditionalChannel;
use App\Article;
use App\Channel;
use App\ChannelName;
use App\Genre;
use App\Helpers\PermissionsHelper;
use App\Helpers\ViewsHelper;
use App\InterprogramPackage;
use App\Picture;
use App\Program;
use App\Record;
use App\User;
use Carbon\Carbon;

class ChannelsController extends Controller {

    public function show($url) {
        $channel = Channel::where(['url' => $url])->first();
        if (!$channel) {
            $channel = Channel::where(['id' => $url])->first();
        }
        if (!$channel) {
            return redirect("https://staroetv.su/");
        }
        $programs = $channel->programs;
        $additional = $channel->additionalPrograms;
        foreach ($additional as $program) {
            $additional_channel_data = AdditionalChannel::where(['program_id' => $program->id, 'channel_id' => $channel->id])->first();
             if ($additional_channel_data->title != '') {
                $program->name = $additional_channel_data->title;
            }
        }
        $programs = $programs->merge($additional);
        $programs = $programs->filter(function($program) {
            return !$program->pending || $program->can_edit;
        });

        $genre_ids = $programs->sortBy('order')->pluck('genre_id')->unique();
        $genres = Genre::whereIn('id', $genre_ids)->get();
        foreach ($genres as &$genre) {
            $genre->programs = $programs->filter(function($program) use ($genre) {
                return $program->genre_id == $genre->id;
            });
        }
        $no_genre_programs = $programs->filter(function($program) {
            return $program->genre_id == null;
        });
        if (count($no_genre_programs) > 0) {
            $no_genre = (object)[
                'id' => -1,
                'url' => 'unspecified',
                'name' => 'Другое',
                'programs' => $no_genre_programs
            ];
            $genres->push($no_genre);
        }
        $popular_programs = Program::where(['channel_id' => $channel->id])->orderBy('views', 'desc')->limit(25)->get();
        $genres->prepend((object)[
            'id' => -2,
            'url' => 'popular',
            'name' => 'Популярные',
            'programs' => $popular_programs
        ]);
        $interprogram_packages = $channel->interprogramPackages;
        foreach ($interprogram_packages as $interprogram_package) {
            //$interprogram_package->records = $interprogram_package->records->shuffle();
        }
        $random_records = Record::where(['channel_id' => $channel->id])->whereNull('program_id')->whereNull('interprogram_package_id')->where(function($q) {
            $q->whereNotIn('interprogram_type', [11, 22]);
            $q->orWhereNull('interprogram_type');
        })->inRandomOrder()->get();
        $random_record = $random_records->filter(function($record) {
            return $record->cover && $record->cover != '';
        })->first();
        if (count($channel->interprogramRecords) > 0) {
            $interprogram_packages->push(new InterprogramPackage([
                'id' => 0,
                'name' => 'Прочее',
                'pictures' => [],
                'years_range' => '',
                'channel_id' => $channel->id,
                'url' => 'other',
                'coverPicture' => new Picture([
                    'url' => $random_record ? $random_record->cover : ''
                ]),
                'records' => collect([])
            ]));
        }
        ViewsHelper::increment($channel, 'channels');
        return view("pages.channels.show", [
            'channel' => $channel,
            'programs' => $genres,
            'interprogram_packages' => $interprogram_packages,
            'records_conditions' => ['channel_id' => $channel->id, 'is_advertising' => false, 'is_radio' => $channel->is_radio],
            'records_conditions_interprogram' => ['channel_id' => $channel->id, 'is_interprogram' => true, 'is_radio' => $channel->is_radio]
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
        if (!$channel) {
            return redirect("https://staroetv.su/video");
        }
        if (!$channel->can_edit) {
            return view("pages.errors.403");
        }
        $is_radio = $channel->is_radio;
        $all_channels = Channel::where(['is_radio' => $channel->is_radio])->where('id', '!=', $id)->get();
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
            'background' => 'sometimes',
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
        if (request()->has('url') &&  request()->input('url') != '') {
            $same_url_channel = Channel::where(['url' => request()->input('url')])->first();
            if ($same_url_channel && $same_url_channel->id != $channel->id) {
                $error = \Illuminate\Validation\ValidationException::withMessages([
                    'url' => ['Канал с таким URL уже существует'],
                ]);
                throw $error;
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

        if (request()->input('is_advertising')) {
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
            'redirect_to' => '/video'
        ];
    }

    public function getAjaxList($is_radio = false) {
        $channels = Channel::where(['is_radio' => $is_radio])->with(['names', 'logo'])->orderBy('is_federal', 'desc')->orderBy('order', 'asc')->get();
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
        $programs = Program::where(['channel_id' => $channel->id])->with('coverPicture')->get();
        $programs = $programs->merge($channel->additionalPrograms);
        return [
            'status' => 1,
            'data' => [
                'programs' => $programs
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
        if (request()->input('_from_confirm_form')) {
            return [
                'status' => 1,
                'text' => 'Канал удален',
                'redirect_to' => '/video'
            ];
        } else {
            return [
                'status' => 1,
                'text' => 'Канал удален'
            ];
        }
    }

    public function approve() {
        $channel = Channel::find(request()->input('id'));
        if (!$channel) {
            return [
                'status' => 0,
                'text' => 'Канал не найден'
            ];
        }
        $can_approve = PermissionsHelper::allows('contentapprove');
        if ($can_approve) {
            $status = request()->input('status', !$channel->pending);
            $channel->pending = $status;
            $channel->save();
            return [
                'status' => 1,
                'data' => [
                    'approved' => !$status
                ]
            ];
        } else {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }
    }

    public function autocomplete() {
        $count = 30;
        $channels = Channel::select('id', 'name', 'is_radio')->orderBy('id', 'asc');
        if (request()->has('term')) {
            $channels = $channels->where('name', 'LIKE', '%'.request()->input('term').'%');
        }
        $total = $channels->count();
        $page = request()->input('page', 1);
        $channels = $channels->limit($count)->offset($count * ($page - 1))->get();
        return [
            'status' => 1,
            'data' => [
                'total' => $total,
                'channels' => $channels
            ]
        ];
    }
}
