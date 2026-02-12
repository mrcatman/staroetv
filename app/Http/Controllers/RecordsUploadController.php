<?php

namespace App\Http\Controllers;

use App\Helpers\MediaHelper;
use App\Helpers\PermissionsHelper;
use App\Jobs\ConvertVideo;
use App\Jobs\DownloadExternalVideo;
use App\Models\Record;
use Illuminate\Support\Facades\Storage;

class RecordsUploadController extends Controller
{

    public function config()
    {
        $can_upload = PermissionsHelper::allows('viupload') && !PermissionsHelper::isBanned();
        $upload_endpoint = config('site.upload_endpoint');
        return [
            'status' => 1,
            'data' => [
                'can_upload' => $can_upload,
                'upload_endpoint' => $upload_endpoint,
            ]
        ];
    }

    public function process()
    {
        if (!PermissionsHelper::allows('viupload') || PermissionsHelper::isBanned()) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }

        $storage = Storage::disk('media-storage');

        $upload_path = "uploads/" . request()->input('server_upload_id');
        if (!$storage->exists($upload_path)) {
            return [
                'status' => 0,
                'text' => 'Ошибка загрузки: файл не найден. Повторите загрузку ещё раз'
            ];
        }

        $meta = json_decode($storage->get($upload_path . ".info"));

        $original_filename = $meta->MetaData->filename;
        $extension = last(explode(".", $original_filename));
        $filename = uniqid() . "." . $extension;

        $is_radio = !!request()->input('is_radio', false);
        $new_path = ($is_radio ? "radio-recordings" : "videos") . "/" . $filename;

        $thumbnail = null;
        if (!$is_radio) {
            $thumbnail = MediaHelper::makeThumbnail($storage->path($upload_path));
        }

        if ($extension != "mp4" && !$is_radio) {
            ConvertVideo::dispatch($upload_path, $new_path);
        } else {
            $storage->move($upload_path, $new_path);
        }

        return [
            'status' => 1,
            'text' => 'Запись загружена',
            'data' => [
                'url' => "/$new_path",
                'thumbnail' => $thumbnail,
            ]
        ];
    }

    public function download()
    {
        $record = Record::find(request()->input('id'));
        if (!$record) {
            return ['status' => 0, 'text' => 'Запись не найдена'];
        }

        if ($record->use_own_player) {
            return ['status' => 0, 'text' => 'Запись уже находится на сервере'];
        }

        if (!$record->original_url) {
            return ['status' => 0, 'text' => 'Не найден исходный URL видео для скачивания'];
        }
        DownloadExternalVideo::dispatch($record);

        return [
            'status' => 1,
            'text' => 'Задание на скачивание добавлено в очередь',
            'redirect_to' => $record->url
        ];
    }
}
