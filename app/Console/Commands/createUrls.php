<?php

namespace App\Console\Commands;
use App\Article;
use Illuminate\Console\Command;

class createUrls extends Command
{

    protected $signature = 'articles:urls';

    protected $description = 'Command description';

    public function __construct()
    {
        parent::__construct();
    }

    protected function rus2translit($string) {
        $converter = [
            'а' => 'a',   'б' => 'b',   'в' => 'v',
            'г' => 'g',   'д' => 'd',   'е' => 'e',
            'ё' => 'e',   'ж' => 'zh',  'з' => 'z',
            'и' => 'i',   'й' => 'y',   'к' => 'k',
            'л' => 'l',   'м' => 'm',   'н' => 'n',
            'о' => 'o',   'п' => 'p',   'р' => 'r',
            'с' => 's',   'т' => 't',   'у' => 'u',
            'ф' => 'f',   'х' => 'h',   'ц' => 'c',
            'ч' => 'ch',  'ш' => 'sh',  'щ' => 'sch',
            'ь' => '\'',  'ы' => 'y',   'ъ' => '\'',
            'э' => 'e',   'ю' => 'yu',  'я' => 'ya',
        ];
        return strtr($string, $converter);
    }
    protected function str2url($str) {
        $str = mb_strtolower($str, "UTF-8");
        $str = $this->rus2translit($str);
        $str = preg_replace('~[^-a-z0-9_]+~u', '-', $str);
        $str = trim($str, "-");
        return $str;
    }


    public function handle() {
        $articles = Article::whereNull('url')->get();
        foreach ($articles as $article) {
            $translit = $this->str2url($article->title);
            var_dump($translit);
            $article->url = $translit;
            $article->save();
        }

    }
}
