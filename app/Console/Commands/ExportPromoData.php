<?php

namespace App\Console\Commands;

use App\Models\Channel;
use App\Models\Genre;
use App\Models\Program;
use App\Models\Record;
use Illuminate\Console\Command;

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


        $channels = Channel::where(['is_radio' => false])->whereNull('country')->orderBy('order', 'ASC')->get();
        foreach ($channels as $channel) {
            $names = $channel->names->map(function ($name) {
                $logo = $name->logo ? $name->logo->full_url : null;
                return [$name->name, $name->date_start, $name->date_end, $logo];
            });

            $data['channels'][] = [
                $channel->id,
                $channel->name,
                $channel->logo_url,
                $channel->is_federal,
                $channel->city,
                $channel->order,
                $names,
            ];
        }
        echo 'Exported '.count($data['channels']).' channels'.PHP_EOL;

        $count = 0;
        Program::whereIn('channel_id', Channel::where(['is_federal' => true])->pluck('id'))->where('views', '>', 1000)->has('records', '>=', 2)->chunk(100, function ($programs) use (&$count, &$data) {
            foreach ($programs as $program) {
                $data['programs'][] = [
                    $program->id,
                    $program->name,
                    $program->cover_url,
                    $program->genre_id
                ];
            }
            $count+= count($programs);
            echo 'Exporting programs... '.$count.PHP_EOL;
        });
        echo 'Exported '.count($data['programs']).' programs'.PHP_EOL;

        $genres = Genre::where(['type' => 'programs'])->get();
        foreach ($genres as $genre) {
            $data['genres'][] = [
                $genre->id,
                $genre->name,
            ];
        }
        echo 'Exported '.count($data['genres']).' genres'.PHP_EOL;

        file_put_contents(public_path('promo/index.json'), json_encode($data, JSON_UNESCAPED_UNICODE));

        $without_records = $this->option('without-records');
        if ($without_records) {
            return;
        }

        $total_records = Record::whereIn('channel_id', $channels->pluck('id'))->count();
        $parts = 10;

        $count = 0;
        $part_index = 1;
        $records_list = [];

        Record::whereIn('channel_id', $channels->pluck('id'))->inRandomOrder()->chunk(100, function ($records) use (&$count, &$part_index, &$parts, $total_records, &$records_list) {
            foreach ($records as $record) {
                $url = $record->use_own_player ? $record->source_hls : $record->original_url;
                if ($record->telegram_id) {
                    $url = $record->all_telegram_sources[array_rand($record->all_telegram_sources)];
                }
                $records_list[] = [
                    $record->id,
                    $record->title,
                    $url,
                    $record->supposed_date,
                    $record->channel_id,
                    $record->program_id,
                    $record->is_interprogram,
                    $record->is_advertising,
                ];
                if (count($records_list) > $total_records / $parts) {
                    file_put_contents(public_path('promo/records-' . $part_index . '.json'), json_encode($records_list, JSON_UNESCAPED_UNICODE));
                    $records_list = [];
                    $part_index++;
                }
            }
            $count+= count($records);
            echo 'Exporting records... '.$count.PHP_EOL;
        });

        file_put_contents(public_path('promo/records-' . $part_index . '.json'), json_encode($records_list, JSON_UNESCAPED_UNICODE));

        echo 'Exported '.$count.' records'.PHP_EOL;


    }
}
