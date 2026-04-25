<?php

namespace App\Console\Commands;

use App\Helpers\TeletextHelper;
use App\Models\Channel;
use App\Models\Teletext;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use function Laravel\Prompts\text;

class ImportTeletext extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'teletext:import {url} {channel} {--username=} {--offset=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import teletext from remote URL';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    private function loadDirectoryListing($url) {
        $response = Http::get($url);

        libxml_use_internal_errors(true);
        $doc = new \DOMDocument();
        $doc->loadHTML($response->getBody()->getContents());
        $list = $doc->getElementsByTagName('tr');

        $links = [];
        for ($i = 3; $i < count($list) - 1; $i++) {
            $tr = $list[$i];
            array_push($links, $tr->getElementsByTagName('a')[0]->getAttribute('href'));
        }
        return $links;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $links = $this->loadDirectoryListing($this->argument('url'));
        $offset = $this->option('offset');
        $links = array_slice($links, $offset);

        $channel = Channel::where(['url' => $this->argument('channel')])->orWhere(['id' => $this->argument('channel')])->first();
        $user = User::where(['username' => $this->option('username')])->first();

        foreach ($links as $link) {
            try {
                preg_match('/%20(\d+).(\d+).(\d+)/', $link, $date_arr);
                if (count($date_arr) >= 4) {
                    $date = Carbon::parse($date_arr[1] . '.' . $date_arr[2] . '.' . $date_arr[3]);
                } else {
                    preg_match('/%20(\d+)/', $link, $date_arr);
                    if (count($date_arr) == 0) {
                        preg_match('/(\d+)/', $link, $date_arr);
                    }
                    $date = Carbon::parse($date_arr[1]);
                }
            } catch (\Exception $e) {
                echo 'Could not parse date: '.$link.PHP_EOL;
                $date_text = text('Enter date');
                $date = Carbon::parse($date_text);
            }
            echo 'Date: '.$date->format('d.m.Y').PHP_EOL;
            if ($date->year > 2010) {
                echo 'Skipping'.PHP_EOL;
                continue;
            }
            $pages = $this->loadDirectoryListing($this->argument('url').$link);
            $pages = array_map(function($page) {
                return explode('.', $page)[0];
            }, array_filter($pages, function($page) {
                return str_contains($page, '.html');
            }));

            $params = [
                'channel_id' => $channel->id,
                'author_id' => $user ? $user->id : null,
                'year' => $date->year,
                'month' => $date->month,
                'day' => $date->day,
                'date' => $date
            ];
            if (!$user) {
                $params['author_username'] = $this->option('username');
            }
            $teletext = Teletext::where($params)->first();
            if (!$teletext) {
                $teletext = new Teletext(array_merge($params, [
                    'pages' => $pages,
                    'pending' => false
                ]));
            }
            $teletext->save();

            $dir = '/teletext-data/'.$teletext->id;
            Storage::disk('temp')->makeDirectory($dir);
            Storage::disk('public_data')->makeDirectory($dir);

            $processed_pages = [];
            foreach ($pages as $page) {
                $dir = '/teletext-data/' . $teletext->id . '/' . $page . '.html';
                $temp_path = Storage::disk('temp')->path($dir);
                $file_path = Storage::disk('public_data')->path($dir);
                if (!file_exists($file_path)) {
                    echo 'Downloading page '.$page.PHP_EOL;
                    Http::sink($temp_path)->get($this->argument('url').$link.'/'.$page.'.html');

                    if (trim(file_get_contents($temp_path)) != '') {
                        TeletextHelper::processPage($teletext, $page);
                        $processed_pages[] = $page;
                    }
                } else {
                    $processed_pages[] = $page;
                }
            }
            echo 'Processed: '.count($pages).' pages'.PHP_EOL;
            Storage::disk('temp')->delete($dir);

            $teletext->pages = $processed_pages;
            $teletext->save();

            TeletextHelper::takeScreenshot($teletext);
            Process::run('chmod 0755 -R '.Storage::disk('public_data')->path($dir));
        }
    }
}
