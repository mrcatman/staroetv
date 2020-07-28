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
            return redirect("/");
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
       // $user = auth()->user();
        $token = config('tokens.vk');
        $owner_id = request()->input('source');
        $data = json_decode(shell_exec(" curl 'https://api.vk.com/method/video.get?count=200&access_token=$token&v=5.101&owner_id=$owner_id&extended=1'"));
        if (isset($data->error)) {
            return [
                'status' => 0,
                'text' => $data->error->error_msg
            ];
        }
        $items = collect($data->response->items)->filter(function($item) {
            return isset($item->player);
        });
        foreach ($items as $item) {
            if (strpos($item->player, "youtu") !== false) {
                preg_match('/(.*)\/embed\/(.*)\?/', $item->player, $output);
                $item->code_fragment = "embed/".$output[2];
            } else {
                preg_match('/(.*)oid=(.*)&id=(.*)&hash(.*)/', $item->player, $output);
                $item->code_fragment = "oid=" . $output[2] . "&id=" . $output[3];
            }
        }
        $codes = $items->pluck('code_fragment');
        $already_added = Record::query();
        foreach ($codes as $code) {
            $already_added = $already_added->orWhere('embed_code', 'LIKE', '%'.$code.'%');
        }
        $already_added = $already_added->get();
        $already_added_codes = $already_added->map(function($player) {
            if (strpos($player, "youtu") !== false) {
                preg_match('/(.*)\/embed\/(.*)\?/', $player, $output);
                return "embed/".$output[2];
            } else {
                preg_match('/(.*)oid=(.*)&id=(.*)&hash(.*)/', $player, $output);
                return "oid=" . $output[2] . "&id=" . $output[3];
            }
        });
        $items = $items->filter(function ($item) use ($already_added_codes) {
            return !$already_added_codes->contains($item->code_fragment);
        });
        $items = $items->values();
        return [
            'status' => 1,
            'data' => [
                'items' => $items
            ]
        ];
    }
}
