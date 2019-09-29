<?php

namespace App\Http\Controllers;

use App\Channel;
use App\Helpers\PermissionsHelper;
use App\Picture;
use App\Program;
use App\Video;
use Carbon\Carbon;

class VideosController extends Controller {

    public function show($id) {
        $video = Video::where(['id' => $id])->first();
        $related_program = null;
        $related_channel = null;
        if ($video->program) {
            $related_program = Video::where(['program_id' => $video->program_id])->inRandomOrder()->limit(6)->get();
        }
        if ($video->channel) {
            $related_channel = Video::where(['channel_id' => $video->channel_id])->inRandomOrder()->limit(6)->get();
        }
        return view("pages.video", [
            'video' => $video,
            'related_program' => $related_program,
            'related_channel' => $related_channel,
        ]);
    }

    public function index() {
        $channels = Channel::all();
        return view("pages.videos", [
            'channels' => $channels,
        ]);
    }

    public function add() {
        return view ("pages.forms.video", [
            'video' => null,
            'channels' => Channel::with('logo', 'names')->get()
        ]);
    }

    public function edit($id) {
        $video = Video::with('channel','program', 'program.coverPicture')->find($id);
        return view ("pages.forms.video", [
            'video' => $video,
            'channels' => Channel::with('logo', 'names')->get()
        ]);
    }


    public function getInfo() {
        if (request()->has('vk_video_id')) {
            $vk_id = request()->input('vk_video_id');
            $token = "3363aef282a9431692b250a6cc2d6a5c101b41d4924a8fad905c28b4e7f7f8762c7d16f4bd75f424f8e47";
            $data = file_get_contents("https://api.vk.com/method/video.get?access_token=$token&v=5.101&videos=$vk_id&extended=1");
            $data = json_decode($data);
            return [
                'status' => 1,
                'data' => [
                    'vk_response' => $data
                ]
            ];
        } elseif (request()->has('youtube_video_id')) {
            $youtube_id = request()->input('youtube_video_id');
            $token = "AIzaSyAU10UC3yFn5SCr0Mgj28nJlgLOG3Gz0Po";
            $data = file_get_contents("https://www.googleapis.com/youtube/v3/videos?id=$youtube_id&key=$token&part=snippet");
            $data = json_decode($data);
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
        $video = new Video([
            'is_from_ucoz' => false,
            'original_added_at' => Carbon::now(),
            'author_username' => $user->username,
            'author_id' => $user->id,
            'description' => '',
            'short_contents' => '',
            'views' => 0
        ]);
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
        if (!request()->input('program.name') && request()->input('program.unknown') !== 'true' && request()->input('is_interprogram') !== 'true') {
            $errors['program'] = "Выберите программу";
        } else {
            if ( request()->input('is_interprogram') !== 'true') {
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
        $video->is_interprogram = request()->input('is_interprogram', false) === "true";
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
