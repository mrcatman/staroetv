<?php

namespace App\Http\Controllers;

use App\Channel;
use App\ChannelName;
use App\Genre;
use App\Helpers\PermissionsHelper;
use App\InterprogramPackage;
use App\Picture;
use App\Program;
use App\Record;
use App\VideoCut;
use Carbon\Carbon;
use function foo\func;

class MassUploadController extends Controller {


    public function index() {
        if (!PermissionsHelper::allows('viadd')) {
            return redirect("https://staroetv.su/");
        }
        return view('pages.mass-upload.index');
    }

    public function fetchList() {
        if (!PermissionsHelper::allows('viadd')) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }
        $items = null;
       // $user = auth()->user();
        $owner_id = request()->input('source');
        $next_page_token = null;

        $files = array_values(array_diff(scandir('/storage/temp-upload'), array('.', '..')));
        if (is_numeric($owner_id)) {
            $type = "vk";
            $token = config('tokens.vk');
            $count = 100;
            $offset = request()->has('next_page_token') ? request()->input('next_page_token') : 0;
            $next_page_token = $offset + $count;
            $vk_url = "https://api.vk.com/method/video.get?count=$count&offset=$offset&access_token=$token&v=5.101&owner_id=$owner_id&extended=1";
            $data = json_decode(shell_exec(" curl '$vk_url'"));
            if (isset($data->error)) {
                return [
                    'status' => 0,
                    'text' => $data->error->error_msg
                ];
            }
            $items = collect($data->response->items)->filter(function ($item) {
                return isset($item->player);
            });
            foreach ($items as $item) {
                if (strpos($item->player, "youtu") !== false) {
                    preg_match('/(.*)\/embed\/(.*)\?/', $item->player, $output);
                    $item->code_fragment = "embed/" . $output[2];
                } else {
                    preg_match('/(.*)oid=(.*)&id=(.*)&hash(.*)/', $item->player, $output);
                    $item->code_fragment = "oid=" . $output[2] . "&id=" . $output[3];
                }
            }
            $codes = $items->pluck('code_fragment');
            $already_added = Record::query();

            foreach ($codes as $code) {
                $already_added = $already_added->orWhere('embed_code', 'LIKE', '%' . $code . '%');
            }
            $already_added = $already_added->get();

            $already_added_codes = $already_added->map(function ($player) {
                if (strpos($player, "youtu") !== false) {
                    preg_match('/(.*)\/embed\/(.*)\?/', $player, $output);
                    return "embed/" . $output[2];
                } else {
                    preg_match('/(.*)oid=(.*)&id=(.*)&hash(.*)/', $player, $output);
                    return "oid=" . $output[2] . "&id=" . $output[3];
                }
            });
            $items = $items->filter(function ($item) use ($already_added_codes) {
                return !$already_added_codes->contains($item->code_fragment);
            });
            $items = $items->values();
        } else {
            $type = "youtube";
            $token = config('tokens.youtube');
            $playlist_data = json_decode(file_get_contents("https://www.googleapis.com/youtube/v3/channels?part=contentDetails&forUsername=$owner_id&key=$token"));
            $uploads_playlist_id = $playlist_data->items[0]->contentDetails->relatedPlaylists->uploads ?? null;
            if (!$uploads_playlist_id) {
               return ['status' => 0, 'text' => 'Не найден плейлист загрузок'];
            }
            $youtube_url = "https://www.googleapis.com/youtube/v3/playlistItems?playlistId=$uploads_playlist_id&key=$token&part=snippet&maxResults=50";
            if (request()->has('next_page_token')) {
                $youtube_url.="&pageToken=".request()->input('next_page_token');
            }
            $data = json_decode(file_get_contents($youtube_url));
            $next_page_token = $data->nextPageToken;
            $items = [];

            $already_added = Record::query();
            foreach ($data->items as $item) {
                $already_added = $already_added->orWhere('embed_code', 'LIKE', '%' . $item->snippet->resourceId->videoId . '%');
            }
            $already_added = $already_added->get();
            $already_added_codes = $already_added->map(function ($record) {
                preg_match('/(.*)\/embed\/(.*)" allowfullscreen/', $record->embed_code, $output);
                return $output[2];
            });
            foreach ($data->items as $item) {
                if (!$already_added_codes->contains($item->snippet->resourceId->videoId)) {
                    $items[] = (object)[
                        'title' => $item->snippet->title,
                        'description' => $item->snippet->description,
                        'player' => 'https://youtube.com/embed/' . $item->snippet->resourceId->videoId,
                        'image' => [
                            [
                                'url' => $item->snippet->thumbnails->high->url
                            ]
                        ]
                    ];
                }
            }
        }
        return [
            'status' => 1,
            'data' => [
                'next_page_token' => $next_page_token,
                'type' => $type,
                'items' => $items,
                'files' => $files,
            ]
        ];
    }

    public function uploadFromDevice() {
        if (!PermissionsHelper::allows('viadd')) {
            return redirect("https://staroetv.su/");
        }
        return view('pages.mass-upload.from-device');
    }

}
