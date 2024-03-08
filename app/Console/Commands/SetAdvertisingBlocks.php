<?php

namespace App\Console\Commands;

use App\Genre;
use App\Record;

use Illuminate\Console\Command;

class SetAdvertisingBlocks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'records:advertisingblocks';

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
      //  dd(Genre::all()->pluck('id', 'name'));
        Record::where('title', 'LIKE', '%екламный бл%')->orWhere('title', 'LIKE', '%реклама%')->where(['is_interprogram' => true, 'is_advertising' => false])->update([
            'interprogram_type' => 22
        ]);
    }
}
