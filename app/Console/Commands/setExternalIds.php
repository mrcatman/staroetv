<?php

namespace App\Console\Commands;
use App\Article;
use App\ArticleCategory;
use App\Helpers\RegexHelper;
use App\Record;
use App\Tag;
use App\TagMaterial;
use Illuminate\Console\Command;

class setExternalIds extends Command
{

    protected $signature = 'videos:ids';

    protected $description = 'Command description';

    public function __construct()
    {
        parent::__construct();
    }
    public function handle() {
        $records = Record::whereNotNull('embed_code')->whereNull('external_id')->get(); //
        foreach ($records as $record) {
            $record->external_id = RegexHelper::getExternalIdFromEmbedCode($record->embed_code);
            dump($record->title.' '.$record->external_id);
            $record->save();
        }
    }
}
