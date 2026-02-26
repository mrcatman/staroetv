<?php

namespace App\Http\Controllers;

use App\Models\Picture;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Intervention\Image\Laravel\Facades\Image;
use Intervention\Image\Exceptions\DecoderException;

class PictureUploadController extends Controller
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
            if ($url_data['host'] == "staroetv.su") {
                $picture_item = Picture::where('url', 'LIKE', '%'.$url_data['path'].'%')->first();
                $already_loaded = true;
            }
            if (!$already_loaded) {
                $picture_item->loadFromURL($url, sha1($url), true, "uploads/" . date("dmY"));
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

    public function upload(Request $request)
    {
        if ($user = auth()->user()) {
            $file = $request->file('picture');
            if ($file) {
                if ($file->getSize() >= $this->maxSize) {
                    return ['status' => 0, 'text' => 'Картинка слишком большая. Попробуйте сжать файл перед загрузкой'];
                }

                $id = ($request->has('id') && $request->input('id') != -1) ? $request->input('id') : $user->id;
                $full_folder = "uploads/" . date("dmY");
                if (!file_exists(public_path("pictures/" . $full_folder))) {
                    mkdir(public_path("pictures/" . $full_folder), 0777, true);
                }

                try {

                    if (!Str::endsWith($file->getClientOriginalName(), "svg")) {
                        $picture = Image::read($file);
                        if ($picture->width() > 900) {
                            $picture->scale(900);
                        }

                        //$mime = explode("/", $picture->origin()->mediaType())[1];
//                        if ($mime == "jpeg") {
//                            $mime = "jpg";
//                        }

                        $filename = $id . "-" . uniqid() . ".webp";
                        $full_path = $full_folder . "/" . $filename;

                        $encoded = $picture->toWebp(quality: 90);
                        $encoded->save(public_path("pictures/" . $full_path));
                    } else {

                        $filename = $id . "-" . uniqid() . ".svg";
                        $full_path = $full_folder . "/" . $filename;

                        Storage::disk('public_data')->putFileAs('pictures/' . $full_folder, $file, $filename);
                    }

                    $picture_item = new Picture([
                        'user_id' => $user->id,
                        'url' => '/pictures/' . $full_path
                    ]);

                    if (request()->has('tag')) {
                        $picture_item->tag = request()->input('tag');
                    }
                    if (request()->has('channel_id')) {
                        $picture_item->channel_id = request()->input('channel_id');
                    }

                    $picture_item->save();
                    return [
                        'status' => 1,
                        'data' => [
                            'picture' => $picture_item
                        ]
                    ];
                } catch (DecoderException $e) {
                    return ['status' => 0, 'text' => 'Формат картинки не распознан'];
                }
            } else {
                return ['status' => 0, 'text' => 'Файл не передан'];
            }
        } else {
            return ['status' => 0, 'text' => 'Ошибка доступа'];
        }
    }

}
