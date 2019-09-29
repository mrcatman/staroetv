<?php

namespace App\Http\Controllers;

use App\Picture;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Intervention\Image\Exception\NotReadableException;

class UploadController extends Controller
{
    public $maxSize = 10485760;

    public function getPicturesByChannel($id) {
        if (!auth()->user() || !auth()->user()->canEditMaterials()) {
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

    public function uploadPictures(Request $request) {
        if ($user = auth()->user()) {
            $file = $request->file('picture');
            if ($file) {
                if ($file->getSize() >= $this->maxSize) {
                    return ['status'=>0,'text'=>'Картинка слишком большая. Попробуйте сжать файл перед загрузкой'];
                }
                try {
                    $picture = Image::make($file);
                    if ($picture->width() > 900) {
                        $picture->resize(900, null, function ($constraint) {
                            $constraint->aspectRatio();
                        });
                    }
                    $mime = explode("/",$picture->mime)[1];
                    if ($mime == "jpeg") {
                        $mime = "jpg";
                    }
                    $id = ($request->has('id') && $request->input('id') != -1) ? $request->input('id') : $user->id;
                    $filename = $id . "-" . uniqid() . "." . $mime;
                    $full_folder = "uploads/" . date("dmY");
                    $full_path = $full_folder . "/" . $filename;
                    if ($mime == "svg") {
                        Storage::disk('public')->put('pictures/'.$full_path, $file);
                    } else{
                        if (!file_exists(public_path("pictures/" . $full_folder))) {
                            mkdir(public_path("pictures/" . $full_folder), 0777, true);
                        }
                        $picture->save(public_path("pictures/" . $full_path), 75);
                    }
                    $picture_item = new Picture();
                    $picture_item->user_id = $user->id;
                    if ($user->canEditMaterials()) {
                        if (request()->has('type')) {
                            $picture_item->type = request()->input('type');
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
