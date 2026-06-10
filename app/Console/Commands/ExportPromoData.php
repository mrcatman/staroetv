<?php

namespace App\Console\Commands;

use App\Models\Channel;
use App\Models\Genre;
use App\Models\Program;
use App\Models\Record;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExportPromoData extends Command
{

    protected $signature = 'promo:export-data {--without-records}';

    public function handle()
    {
        $data = [
            'channels' => [],
            'programs' => [],
            'genres' => [],
        ];
        $channel_index = 1;
        $channels_query = Channel::where(['is_radio' => false])->whereNull('country');

        $regional_counts = Channel::select([DB::raw('count(*) as count'), 'city'])->orderBy('count', 'desc')->whereNotNull('city')->groupBy('city')->get()->pluck('count', 'city');
        $channels = $channels_query->clone()->where(['is_federal' => true])->orderBy('order', 'ASC')->get()
            ->merge($channels_query->clone()->where(['is_federal' => false])->whereNull('city')->orderBy('order', 'ASC')->get())
            ->merge($channels_query->clone()->where(['is_federal' => false])->whereNotNull('city')->orderByRaw("FIELD(city, '" . $regional_counts->keys()->join("','") . "')")->get());


        foreach ($channels as $channel) {
            $names = $channel->names->map(function ($name) {
                $logo = $name->logo ? $name->logo->url : null;
                return [$name->name, $name->date_start, $name->date_end, $logo];
            });
            $years = $channel->records()->pluck('year')->unique()->sort()->filter(function ($year) {
                return $year > 1950;
            })->values();

            $data['channels'][] = [
                $channel->id,
                $channel->name,
                $channel->logo_url,
                $channel->is_federal,
                $channel->city,
                $channel_index,
                $names,
                $years
            ];
            $channel_index++;
        }
        echo 'Exported ' . count($data['channels']) . ' channels' . PHP_EOL;

        $count = 0;
        Program::has('records', '>=', 2)->whereHas('channel', function ($q) {
            return $q->where(['is_radio' => false]);
        })->chunk(100, function ($programs) use (&$count, &$data) {
            foreach ($programs as $program) {
                $years = $program->records()->pluck('year')->unique()->sort()->filter(function ($year) {
                    return $year > 1950;
                })->values();
                $highlight = $program->channel->is_federal && $program->views > 100;

                $channel_ids = [$program->channel_id, $program->additionalChannels->pluck('id')];
                $data['programs'][] = [
                    $program->id,
                    $program->name,
                    $program->cover_url,
                    $program->genre_id,
                    $years,
                    $channel_ids,
                    $highlight
                ];
            }
            $count += count($programs);
            echo 'Exporting programs... ' . $count . PHP_EOL;
        });
        echo 'Exported ' . count($data['programs']) . ' programs' . PHP_EOL;

        $genres = Genre::where(['type' => 'programs'])->get();
        foreach ($genres as $genre) {
            $data['genres'][] = [
                $genre->id,
                $genre->name,
            ];
        }
        echo 'Exported ' . count($data['genres']) . ' genres' . PHP_EOL;

        file_put_contents(public_path('teleport-data/index.json'), json_encode($data, JSON_UNESCAPED_UNICODE));

        $without_records = $this->option('without-records');
        if ($without_records) {
            return;
        }

        $records = Record::where(function ($q) use ($channels) {
            $q->whereIn('channel_id', $channels->pluck('id'));
            $q->orWhere(['is_advertising' => true]);
        })->where(['is_radio' => false])->where(function ($q) {
            $q->where('embed_code', 'NOT LIKE', '%dailymotion%');
            $q->orWhereNull('embed_code');
        })->whereNull('telegram_id')->whereNull('country');

        $total_records = $records->count();
        $parts = 10;

        $count = 0;
        $part_index = 1;
        $records_list = [];

        $program_genres = Program::pluck('genre_id', 'id');

        $seed = date('Ymd');
        $records->inRandomOrder($seed)->chunk(100, function ($records) use (&$count, &$part_index, &$parts, $total_records, &$records_list, $program_genres, &$record_ids) {
            foreach ($records as $record) {
                $url = $record->use_own_player ? $record->source_hls : $record->original_url;

                if (!str_contains($url, 'vk.com') && !str_contains($url, 'youtu') && !str_contains($url, '.m3u8')) { // todo check why rutube iframe api doesn't work in helium
                    continue;
                }

                $count++;
//                if ($record->telegram_id) {
//                    //$url = $record->all_telegram_sources[array_rand($record->all_telegram_sources)];
//                }

                $is_advertising = $record->is_advertising;
                if ($record->is_interprogram && str_contains(mb_strtolower($record->title), 'реклам') && !str_contains(mb_strtolower($record->title), 'рекламная')) {
                    $is_advertising = true;
                }
                $genre_id = isset($program_genres[$record->program_id]) ? $program_genres[$record->program_id] : null;

                $records_list[] = [
                    $record->id,
                    $record->title,
                    $url,
                    $record->supposed_date,
                    $record->year,
                    $record->channel_id,
                    $record->program_id,
                    $record->is_interprogram,
                    $is_advertising,
                    $genre_id,
                ];
                if (count($records_list) > $total_records / $parts) {
                    file_put_contents(public_path('teleport-data/records-' . $part_index . '.json'), json_encode($records_list, JSON_UNESCAPED_UNICODE));
                    $records_list = [];
                    $part_index++;
                }
            }

            echo 'Exporting records... ' . $count . PHP_EOL;
        });

        file_put_contents(public_path('teleport-data/records-' . $part_index . '.json'), json_encode($records_list, JSON_UNESCAPED_UNICODE));

        echo 'Exported ' . $count . ' records' . PHP_EOL;


    }
}
