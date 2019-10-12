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
        $video = Record::where(['id' => $id])->first();
        $related_program = null;
        $related_channel = null;
        if ($video->program) {
            $related_program = Record::where(['program_id' => $video->program_id])->inRandomOrder()->limit(6)->get();
        }
        if ($video->channel) {
            $related_channel = Record::where(['channel_id' => $video->channel_id])->inRandomOrder()->limit(6)->get();
        }
        return view("pages.video", [
            'video' => $video,
            'related_program' => $related_program,
            'related_channel' => $related_channel,
        ]);
    }

    public function showOld($id) {
        $video = Record::where(['ucoz_id' => $id])->first();
        $related_program = null;
        $related_channel = null;
        if ($video->program) {
            $related_program = Record::where(['program_id' => $video->program_id])->inRandomOrder()->limit(6)->get();
        }
        if ($video->channel) {
            $related_channel = Record::where(['channel_id' => $video->channel_id])->inRandomOrder()->limit(6)->get();
        }
        return view("pages.video", [
            'video' => $video,
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
            $data = json_decode(file_get_contents("https://api.vk.com/method/video.get?access_token=$token&v=5.101&videos=$vk_id&extended=1"));
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
        $video = new Record([
            'is_from_ucoz' => false,
            'original_added_at' => Carbon::now(),
            'author_username' => $user->username,
            'author_id' => $user->id,
            'description' => '',
            'short_contents' => '',
            'views' => 0
        ]);
        return $this->fillData($video);
    }

    public function update($id) {
        $video = Record::find($id);
        if (!$video) {
            return [
                'status' => 0,
                'text' => 'Видео не найдено'
            ];
        }
        if (!$video->can_edit) {
           return [
               'status' => 0,
               'text' => 'Ошибка доступа'
           ];
        };
        return $this->fillData($video);
    }

    private function fillData($video) {
        $user = auth()->user();

        $errors = [];
        if (!request()->input('channel.name') && request()->input('channel.unknown') !== 'true') {
            $errors['channel'] = "Выберите канал";
        } else {
            if (request()->input('channel.id') > 0) {
                $video->channel_id = request()->input('channel.id');
            } else {
                $channel = new Channel(['author_id' => $user->id, 'name' => request()->input('channel.name'),'is_regional' => false, 'is_abroad' => false, 'pending' => true]);
                $channel->save();
                $video->channel_id = $channel->id;
            }
        }
        $is_interprogram = request()->input('is_interprogram', false);
        $video->is_interprogram = $is_interprogram === "true" || $is_interprogram == 1;
        if (!request()->input('program.name') && request()->input('program.unknown') !== 'true' && !$is_interprogram) {
            $errors['program'] = "Выберите программу";
        } else {
            if (!$video->is_interprogram) {
                if (request()->input('program.id') > 0) {
                    $video->program_id = request()->input('program.id');
                } else {
                    $program = new Program(['author_id' => $user->id, 'name' => request()->input('program.name'), 'cover' => '', 'channel_id' => $video->id, 'pending' => true]);
                    $program->save();
                    $program->program_id = $program->id;
                }
            }
        }
        if (!request()->input('video.code')) {
            $errors['url'] = "Укажите ссылку на видео";
        } else {
            $video->embed_code = request()->input('video.code');
        }
        if (request()->input('date.year') > 0) {
            $video->year = request()->input('date.year');
        }
        if (request()->input('date.month') > 0) {
            $video->month = request()->input('date.month');
        }
        if (request()->input('date.day') > 0) {
            $video->day = request()->input('date.day');
        }
        if (request()->input('date.year') > 0 && request()->input('date.month') > 0 && request()->input('date.day') > 0) {
            $video->date = Carbon::createFromDate(request()->input('date.year'), request()->input('date.month'), request()->input('date.day'));
        }
        if (request()->input('short_description') != "") {
            $video->short_description = request()->input('short_description');
        }

        if ($video->is_interprogram) {
            if (request()->input('interprogram_package_id') > 0) {
                $video->interprogram_package_id = request()->input('interprogram_package_id');
            }
        }
        if (request()->input('cover') != "") {
            $cover = Picture::where(['url' => request()->input('cover')])->first();
            if ($cover) {
                $video->cover_id = $cover->id;
            } else {
                $cover = new Picture();
                $cover->loadFromURL(request()->input('cover'), md5(request()->input('cover')));
                $cover->save();
                $video->cover_id = $cover->id;
            }
        }
        $video->title = $video->generateTitle();
        if (count($errors) > 0) {
            return [
                'status' => 0,
                'text' => 'В форме есть ошибки',
                'errors' => $errors
            ];
        }
        $video->save();
        return [
            'status' => 1,
            'text' => 'Видео добавлено',
            'data' => [
                'video' => $video
            ]
        ];
    }
}
