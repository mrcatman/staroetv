<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Program;
use App\Models\Record;

class PromoController extends Controller {

    public function index()
    {
        $channels = Channel::where(['is_federal' => true, 'is_radio' => false])->orderBy('order', 'ASC')->get();
        $programs = Program::where('views', '>', 1000)->whereIn('channel_id', $channels->pluck('id'))->inRandomOrder()->limit(12)->get();

        return view ('promo', [
            'channels' => $channels,
            'programs' => $programs
        ]);
    }

    public function randomVideo() {
        $records = Record::approved()->where(['is_radio' => false]);
        if (request()->has('channel_id')) {
            $records = $records->where(['channel_id' => request()->get('channel_id')]);
        }
    // todo: remove foreign, add tapes list with most popular programs

//$records = $records->whereNotNull('source_path');
        //$records = $records->where('embed_code', 'LIKE', '%vk.%');
        //$records = $records->where('embed_code', 'LIKE', '%youtu%')->where(['use_own_player' => false, 'telegram_id' => null]);
        $records = $records->where(['is_interprogram' => false]);
        $record = $records->inRandomOrder()->first();

        $url = $record->use_own_player ? $record->source_hls : $record->original_url;
        if ($record->telegram_id) {
            $url = $record->all_telegram_sources[array_rand($record->all_telegram_sources)];
        }

        return [
            'id' => $record->id,
            'title' => $record->title,
            'channel_name' => $record->channel ? $record->channel_name : '',
            'url' => $url
        ];
    }

}
