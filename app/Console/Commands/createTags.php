<?php

namespace App\Console\Commands;
use App\Article;
use App\ArticleCategory;
use App\Tag;
use App\TagMaterial;
use Illuminate\Console\Command;

class createTags extends Command
{

    protected $signature = 'articles:tags';

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
        $articles = Article::all();
        $tags = [
            'blog' => 'Блог пользователя',
            'article' => 'Статья',
            'news' => 'Новость'
        ];
        $categories = ArticleCategory::all();
        foreach ($categories as $category) {
            $tags[$this->str2url($category->title)] = $category->title;
        }
        $tag_resources = [];
        foreach ($tags as $tag_url => $tag_name) {
            $tag = Tag::firstOrCreate(['name' => $tag_name, 'url' => $tag_url]);
            $tag_resources[$tag_url] = $tag;
        }
        foreach ($articles as $article) {
            if ($article->type_id == Article::TYPE_ARTICLES) {
                $tag_url = 'article';
            } elseif ($article->type_id == Article::TYPE_BLOG) {
                $tag_url = 'blog';
            } elseif ($article->type_id == Article::TYPE_NEWS) {
                $tag_url = 'news';
            }
            TagMaterial::firstOrCreate([
                'tag_id' => $tag_resources[$tag_url]->id,
                'material_id' => $article->id,
                'material_type' => 'articles'
            ]);
            $category = $article->category;
            if ($category) {
                $category_tag_url = $this->str2url($category->title);
                $tag_id = $tag_resources[$category_tag_url]->id;
                if ($tag_id != 8 && $tag_id != 10 && $tag_id != 11) {
                    TagMaterial::firstOrCreate([
                        'tag_id' => $tag_id,
                        'material_id' => $article->id,
                        'material_type' => 'articles'
                    ]);
                }
            }
        }

    }
}
