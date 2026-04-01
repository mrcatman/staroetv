<?php

namespace App\Console\Commands;

use App\Models\Picture;
use App\Models\Record;
use Illuminate\Console\Command;

class FixBrokenThumbnailUrls extends Command
{

    protected $signature = 'pictures:fix-broken-thumbnail-urls';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $broken = Picture::where('url' ,'like', '%frameborder%')->get();
        foreach ($broken as $picture) {
            $record = Record::where(['cover_id' => $picture->id])->first();
            if (!$record) {
                continue;
            }
            $picture->url = str_replace('" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>', '', $picture->url);
            $picture->save();
        }
    }
}
