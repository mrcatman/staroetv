<?php

namespace App\Http\Controllers;

use App\Picture;
use Illuminate\Support\Str;
use function GuzzleHttp\Psr7\str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Intervention\Image\Exception\NotReadableException;

class UploadController extends Controller
{
    public $maxSize = 10485760;

    public function getPicturesByChannel($id) {
        if (!auth()->user()) { //!auth()->user()->canEditMaterials()
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }
        $pictures = Picture::where(['channel_id' => $id])->get();
        return [
            'status' => 1,
            'data' => [
                'pictures' => $pictures
            ]
        ];
    }

    public function uploadPicturesByURL()
    {
        if ($user = auth()->user()) {
            $picture_item = new Picture(['user_id' => $user->id]);
            $url = trim(request()->input('url'));
            if ($url == "") {
                return ['status'=>0,'text'=>'Введите адрес'];
            }
            $already_loaded = false;
            $url_data = parse_url($url);
            if ($url_data['host'] == "staroetv.mrcatmann.ru") {
                $picture_item = Picture::where('url', 'LIKE', '%'.$url_data['path'].'%')->first();
                $already_loaded = true;
            }
            if (!$already_loaded) {
                $picture_item->loadFromURL($url, md5($url), true, "uploads/" . date("dmY"));
                if (request()->has('tag')) {
                    $picture_item->tag = request()->input('tag');
                }
                if (request()->has('channel_id')) {
                    $picture_item->channel_id = request()->input('channel_id');
                }
                $picture_item->save();
            }
            return [
                'status' => 1,
                'text' => 'Картинка сохранена',
                'data' => [
                    'picture' => $picture_item
                ]
            ];
        }  else {
            return ['status'=>0,'text'=>'Ошибка доступа'];
        }
    }

    public function uploadPictures(Request $request) {
        if ($user = auth()->user()) {
            $file = $request->file('picture');
            if ($file) {
                if ($file->getSize() >= $this->maxSize) {
                    return ['status'=>0,'text'=>'Картинка слишком большая. Попробуйте сжать файл перед загрузкой'];
                }
                try {
                    if (!Str::endsWith($file->getClientOriginalName(), "svg")) {
                        $picture = Image::make($file);
                        if ($picture->width() > 900) {
                            $picture->resize(900, null, function ($constraint) {
                                $constraint->aspectRatio();
                            });
                        }
                        $mime = explode("/", $picture->mime)[1];
                        if ($mime == "jpeg") {
                            $mime = "jpg";
                        }
                        $id = ($request->has('id') && $request->input('id') != -1) ? $request->input('id') : $user->id;
                        $filename = $id . "-" . uniqid() . "." . $mime;
                        $full_folder = "uploads/" . date("dmY");
                        $full_path = $full_folder . "/" . $filename;
                        if (!file_exists(public_path("pictures/" . $full_folder))) {
                            mkdir(public_path("pictures/" . $full_folder), 0777, true);
                        }
                        $picture->save(public_path("pictures/" . $full_path), 75);
                    } else {
                        $id = ($request->has('id') && $request->input('id') != -1) ? $request->input('id') : $user->id;
                        $filename = $id . "-" . uniqid() . ".svg";
                        $full_folder = "uploads/" . date("dmY");
                        $full_path = $full_folder . "/" . $filename;
                        if (!file_exists(public_path("pictures/" . $full_folder))) {
                            mkdir(public_path("pictures/" . $full_folder), 0777, true);
                        }
                        Storage::disk('public_data')->putFileAs('pictures/'.$full_folder, $file, $filename);
                    }
                    $picture_item = new Picture();
                    $picture_item->user_id = $user->id;
                    if ($user) {
                        if (request()->has('tag')) {
                            $picture_item->tag = request()->input('tag');
                        }
                        if (request()->has('channel_id')) {
                            $picture_item->channel_id = request()->input('channel_id');
                        }
                    }
                    $picture_item->url = "/pictures/" . $full_path;
                    $picture_item->save();
                    return [
                        'status' => 1,
                        'data' => [
                            'picture' => $picture_item
                        ]
                    ];
                } catch (NotReadableException $e) {
                    return ['status'=>0,'text'=>'Формат картинки не распознан'];
                }
            } else {
                return ['status' => 0, 'text' => 'Файл не передан'];
            }
        } else {
            return ['status'=>0,'text'=>'Ошибка доступа'];
        }
    }
}
