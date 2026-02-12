<?php

namespace App\Http\Controllers;

use App\Helpers\MediaHelper;
use App\Helpers\PermissionsHelper;
use App\Jobs\ConvertVideo;
use App\Jobs\DownloadExternalVideo;
use App\Models\Picture;
use App\Models\Record;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Process;
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
        $duration = MediaHelper::getDuration($storage->path($upload_path));

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
                'duration' => $duration,
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

    public function onDownloaded($id)
    {
        $ips = explode(',', config('site.media_server_ips'));
        if (!in_array(request()->ip(), $ips)) {
            abort(403);
        }
        $record = Record::find($id);
        if (!$record) {
            abort(404);
        }

        $storage = Storage::disk('media-storage');
        $path = "/videos/$id.mp4";
        if (!$storage->exists($path)) {
            abort(400);
        }

        $thumbnail = MediaHelper::makeThumbnail($storage->path($path));
        $cover = Picture::firstOrNew([
            'url' => $thumbnail
        ]);
        $cover->save();

        $record->use_own_player = true;
        $record->source_type = "local";
        $record->source_path = $path;
        $record->cover_id = $cover->id;
        $record->save();

        Cache::forget('record_' . $record->id);
        Cache::forget('record_cover_' . $record->id);

        return ['status' => 1];
    }
}
