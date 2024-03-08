<?php

namespace App\Console\Commands;

use App\Picture;
use App\Record;
use Illuminate\Console\Command;

class changePngToJpg extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pictures:changepaths';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $records = Record::where('original_cover', 'LIKE', '%video_covers%')->get();
        foreach ($records as $record) {
            $record->original_cover = str_replace(".png", ".jpg", $record->original_cover);
            $record->save();
        }
        $pictures = Picture::where('url', 'LIKE', '%video_covers%')->get();
        foreach ($pictures as $picture) {
            $picture->url = str_replace(".png", ".jpg", $picture->url);
            $picture->save();
        }

    }
}
