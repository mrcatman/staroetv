<?php

namespace App\Http\Controllers;

use App\Channel;
use App\Helpers\PermissionsHelper;
use App\Picture;
use App\Program;
use App\Record;
use Carbon\Carbon;

class RecordsController extends Controller {

    public function show($id) {
        $record = Record::where(['id' => $id])->first();
        $related_program = null;
        $related_channel = null;
        if ($record->program) {
            $related_program = Record::where(['program_id' => $record->program_id])->inRandomOrder()->limit(6)->get();
        }
        if ($record->channel) {
            $related_channel = Record::where(['channel_id' => $record->channel_id])->inRandomOrder()->limit(6)->get();
        }
        return view("pages.records.show", [
            'record' => $record,
            'related_program' => $related_program,
            'related_channel' => $related_channel,
        ]);
    }

    public function showOld($id) {
        $record = Record::where(['ucoz_id' => $id])->first();
        $related_program = null;
        $related_channel = null;
        if ($record->program) {
            $related_program = Record::where(['program_id' => $record->program_id])->inRandomOrder()->limit(6)->get();
        }
        if ($record->channel) {
            $related_channel = Record::where(['channel_id' => $record->channel_id])->inRandomOrder()->limit(6)->get();
        }
        return view("pages.records.show", [
            'record' => $record,
            'related_program' => $related_program,
            'related_channel' => $related_channel,
        ]);
    }


    public function index($params) {
        $federal = Channel::where(['is_federal' => true])->where($params)->orderBy('order', 'ASC')->get();
        $regional = Channel::where(['is_regional' => true])->where($params)->orderBy('order', 'ASC')->get();
        $abroad = Channel::where(['is_abroad' => true])->where($params)->orderBy('order', 'ASC')->get();
        $other = Channel::where(['is_federal' => false, 'is_regional' => false, 'is_abroad' => false])->where($params)->orderBy('order', 'ASC')->get();
        $cities = [];
        foreach ($regional as $channel) {
            if (!isset($cities[$channel->city])) {
                $cities[$channel->city] = 1;
            } else {
                $cities[$channel->city]++;
            }
        }
        arsort($cities);
        return view("pages.records.index", [
            'cities' => $cities,
            'data' => $params,
            'federal' => $federal,
            'regional' => $regional,
            'abroad' => $abroad,
            'other' => $other,
        ]);
    }

    public function add($params) {
        return view ("pages.forms.record", [
            'data' => $params,
            'record' => null,
            'channels' => Channel::with('logo', 'names')->where($params)->get()
        ]);
    }

    public function edit($id) {
        $record = Record::with('channel','program', 'program.coverPicture')->find($id);
        return view ("pages.forms.record", [
            'data' => [
                'is_radio' => $record->is_radio
            ],
            'record' => $record,
            'channels' => Channel::with('logo', 'names')->get()
        ]);
    }


    public function getInfo() {
        if (request()->has('vk_video_id')) {
            $vk_id = request()->input('vk_video_id');
            $token = config('tokens.vk');
            $data = json_decode(file_get_contents("https://api.vk.com/method/record.get?access_token=$token&v=5.101&videos=$vk_id&extended=1"));
            return [
                'status' => 1,
                'data' => [
                    'vk_response' => $data
                ]
            ];
        } elseif (request()->has('youtube_video_id')) {
            $youtube_id = request()->input('youtube_video_id');
            $token = config('tokens.youtube');
            $data = json_decode(file_get_contents("https://www.googleapis.com/youtube/v3/videos?id=$youtube_id&key=$token&part=snippet"));
            return [
                'status' => 1,
                'data' => [
                    'youtube_response' => $data
                ]
            ];
        } else {
            return [
                'status' => 0,
                'text' => 'Не передан ID видео'
            ];
        }
    }

    public function save() {
        if (!PermissionsHelper::allows('viadd')) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }
        $user = auth()->user();
        $record = new Record([
            'is_from_ucoz' => false,
            'original_added_at' => Carbon::now(),
            'author_username' => $user->username,
            'author_id' => $user->id,
            'description' => '',
            'short_contents' => '',
            'views' => 0
        ]);
        return $this->fillData($record);
    }

    public function update($id) {
        $record = Record::find($id);
        if (!$record) {
            return [
                'status' => 0,
                'text' => 'Видео не найдено'
            ];
        }
        if (!$record->can_edit) {
           return [
               'status' => 0,
               'text' => 'Ошибка доступа'
           ];
        };
        return $this->fillData($record);
    }

    private function fillData($record) {
        $user = auth()->user();
        $is_radio = request()->input('is_radio', false);
        $errors = [];
        if (!request()->input('channel.name') && request()->input('channel.unknown') !== 'true') {
            if ($is_radio) {
                $errors['channel'] = "Выберите радиостанцию";
            } else {
                $errors['channel'] = "Выберите канал";
            }
        } else {
            if (request()->input('channel.id') > 0) {
                $record->channel_id = request()->input('channel.id');
            } else {
                $channel = new Channel(['author_id' => $user->id, 'name' => request()->input('channel.name'),'is_regional' => false, 'is_abroad' => false, 'pending' => true]);
                $channel->save();
                $record->channel_id = $channel->id;
            }
        }
        $is_interprogram = request()->input('is_interprogram', false);
        $record->is_interprogram = $is_interprogram === "true" || $is_interprogram == 1;
        if (!request()->input('program.name') && request()->input('program.unknown') !== 'true' && !$is_interprogram) {
            $errors['program'] = "Выберите программу";
        } else {
            if (!$record->is_interprogram) {
                if (request()->input('program.id') > 0) {
                    $record->program_id = request()->input('program.id');
                } else {
                    $program = new Program(['author_id' => $user->id, 'name' => request()->input('program.name'), 'cover' => '', 'channel_id' => $record->id, 'pending' => true]);
                    $program->save();
                    $program->program_id = $program->id;
                }
            }
        }
        if (!request()->input('record.code')) {
            $errors['url'] = "Укажите ссылку на видео";
        } else {
            $record->embed_code = request()->input('record.code');
        }
        if (request()->input('date.year') > 0) {
            $record->year = request()->input('date.year');
        }
        if (request()->input('date.month') > 0) {
            $record->month = request()->input('date.month');
        }
        if (request()->input('date.day') > 0) {
            $record->day = request()->input('date.day');
        }
        if (request()->input('date.year') > 0 && request()->input('date.month') > 0 && request()->input('date.day') > 0) {
            $record->date = Carbon::createFromDate(request()->input('date.year'), request()->input('date.month'), request()->input('date.day'));
        }
        if (request()->input('short_description') != "") {
            $record->short_description = request()->input('short_description');
        }

        if ($record->is_interprogram) {
            if (request()->input('interprogram_package_id') > 0) {
                $record->interprogram_package_id = request()->input('interprogram_package_id');
            }
        }
        if (request()->input('cover') != "") {
            $cover = Picture::where(['url' => request()->input('cover')])->first();
            if ($cover) {
                $record->cover_id = $cover->id;
            } else {
                $cover = new Picture();
                $cover->loadFromURL(request()->input('cover'), md5(request()->input('cover')));
                $cover->save();
                $record->cover_id = $cover->id;
            }
        }
        $record->title = $record->generateTitle();
        if (count($errors) > 0) {
            return [
                'status' => 0,
                'text' => 'В форме есть ошибки',
                'errors' => $errors
            ];
        }
        $record->save();
        return [
            'status' => 1,
            'text' => $is_radio ? 'Радиозапись добавлена' : 'Видео добавлено',
            'data' => [
                'record' => $record
            ]
        ];
    }

    public function search($params) {
        $is_radio = $params['is_radio'];
        $params = request()->all();
        $search = request()->input('search');
        $records = Record::where(['is_radio' => $is_radio])->where('title', 'LIKE', '%'.$search.'%');
        if (request()->has('sort')) {
            $sort = request()->input('sort');
            $order = request()->input('sort_order', 'desc');
            $records = $records->orderBy($sort, $order);
        }
        $records_count = $records->count();
        $records = $records->paginate(30);
        $data = [
            'params' => $params,
            'records' => $records->appends(request()->except('page')),
            'records_count' => $records_count,
            'is_radio' => $is_radio
        ];
        if (request()->isMethod('post')) {
            return $data;
        }
        return view("pages.records.search", $data);
    }
}
