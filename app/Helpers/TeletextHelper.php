<?php

namespace App\Helpers;

use App\Models\Picture;
use App\Models\Teletext;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Spatie\Browsershot\Browsershot;
use Spatie\LaravelScreenshot\Facades\Screenshot;

class TeletextHelper {
    public static function process(Teletext $teletext): void
    {
        $dir = '/teletext-data/'.$teletext->id;
        Storage::disk('temp')->makeDirectory($dir);
        Storage::disk('public_data')->makeDirectory($dir);

        $output_path = Storage::disk('temp')->path($dir);
        $file_path = Storage::disk('temp')->path('teletext/temp_'.$teletext->id.'.t42');

        Process::path(config('site.teletext.cwd'))->run('python3 -m teletext html "'.$output_path.'/" "'.$file_path.'" --localcodepage=cyr');
        Log::info('python3 -m teletext html "'.$output_path.'/" "'.$file_path.'"');
        $pages = Storage::disk('temp')->allFiles($dir);
        $pages = array_map(function($page) {
            $path = explode('/',$page);
            $filename = array_pop($path);
            return explode('.', $filename)[0];
        }, $pages);

        foreach ($pages as $page) {
            self::processPage($teletext, $page);
        }

        Storage::disk('temp')->delete($dir);

        $teletext->pages = $pages;
        $teletext->save();

        Process::run('chmod 0755 -R '.Storage::disk('public_data')->path($dir));
        self::takeScreenshot($teletext);

        Process::run('chmod 0755 -R '.Storage::disk('public_data')->path($dir));
    }

    public static function processPage(Teletext $teletext, string $page): void
    {
        $path = '/teletext-data/'.$teletext->id.'/'.$page.'.html';
        $temp_path = Storage::disk('temp')->path($path);
        $file_path = Storage::disk('public_data')->path($path);

        $subpages = '';

        libxml_use_internal_errors(true);
        $doc = new \DOMDocument();
        $content = file_get_contents($temp_path);
        $doc->loadHTML($content);
        $body = $doc->getElementsByTagName('body')[0];

        $links = $doc->getElementsByTagName('a');
        foreach ($links as $link) {
            $href = $link->getAttribute('href');
            $link->setAttribute('href', '?page='.explode('.', $href)[0]);
        }

        $children = $body->childNodes;
        foreach ($children as $child)
        {
            $subpages .= $body->ownerDocument->saveHTML($child);
        }

        file_put_contents($file_path, $subpages);
    }

    public static function takeScreenshot(Teletext $teletext): void {
        if (!$teletext->pages || count($teletext->pages) === 0) {
            return;
        }
        $page = in_array('100', $teletext->pages) ? '100' : $teletext->pages[0];
        $thumbnail = '/teletext-data/'.$teletext->id.'/'.$page.'.png';
        Screenshot::url(config('app.url').'/teletext/'.$teletext->id.'?page='.$page.'&inline=true')
            ->withBrowsershot(function (Browsershot $browsershot) {
                $browsershot->waitUntilNetworkIdle();
                $browsershot->ignoreHttpsErrors();
                $browsershot->windowSize(640, 480);
            })
            ->size(640, 480)
            ->disk('public_data')
            ->save($thumbnail);

        $cover = Picture::firstOrNew([
            'url' => $thumbnail
        ]);
        $cover->save();

        $teletext->cover_id = $cover->id;
        $teletext->save();

        Process::run('chmod 0755 '.Storage::disk('public_data')->path($thumbnail));

        Cache::forget('teletext_cover_'.$teletext->id);
    }

}
