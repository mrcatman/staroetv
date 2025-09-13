<?php

namespace App\Helpers;

use App\Models\Teletext;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

class TeletextHelper {
    public static function process(Teletext $teletext, UploadedFile $file): void
    {
        $file_path = $file->getRealPath();

        $dir = '/teletext/'.$teletext->id;
        Storage::disk('temp')->makeDirectory($dir);
        Storage::disk('public_data')->makeDirectory($dir);

        $output_path = Storage::disk('temp')->path($dir);
        Process::path(config('teletext.cwd'))->run('python3 -m teletext html "'.$output_path.'/" "'.$file_path.'"');

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
    }


    private static function processPage(Teletext $teletext, string $page): void
    {
        $dir = '/teletext/'.$teletext->id.'/'.$page.'.html';
        $temp_path = Storage::disk('temp')->path($dir);
        $file_path = Storage::disk('public_data')->path($dir);

        $subpages = '';

        libxml_use_internal_errors(true);
        $doc = new \DOMDocument();
        $doc->loadHTML(file_get_contents($temp_path));
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

}
