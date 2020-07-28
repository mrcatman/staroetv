<?php

namespace App\Console\Commands;

use App\Article;
use App\Helpers\CSVHelper;
use App\UserAward;
use App\UserReputation;
use Carbon\Carbon;
use Illuminate\Console\Command;

class importArticles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'articles:import';

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
        $news = CSVHelper::transform(public_path("data/news.csv"), [
           'original_id', 'category_id', 'year', 'month', 'day', '', 'pending', '', 'created_at', '', 'username', 'title', 'short_content', 'content', '', '', 'views', '', '',  '', 'ip', 'source', 'cover', 'cover_text', '', '', 'user_id', '', 'path', 'updated_at'
        ], false);
        $articles = CSVHelper::transform(public_path("data/blog.csv"), [
            'original_id', 'category_id', 'year', 'month', 'day', '', 'pending', '', 'created_at', '', 'username', 'title', 'short_content', 'content', '', '', 'views', '', '',  '', 'ip', '', 'cover', 'source', '', '', 'user_id', '', 'path', 'updated_at'
        ], false);
        $blog = CSVHelper::transform(public_path("data/stuff.csv"), [
            'original_id', '', 'category_id', '', '', 'created_at', '', '', '', '', '', '', 'ip', 'views', '', '', 'title', 'short_content', 'content', '', '', '', '', 'username', '', '', 'source', '', '', '', '', '', '', '', 'cover', '', '', '', '', '', '', 'user_id', '', '', 'path', 'updated_at'
        ], false);
        //file_put_contents(public_path("data/news.txt"), json_encode($news, JSON_UNESCAPED_UNICODE));
        foreach ($news as $news_item) {
            unset($news_item['']);
            $created_at = $news_item['created_at'];
            unset($news_item['created_at']);
            $updated_at = $news_item['updated_at'];
            unset($news_item['updated_at']);
            $article_obj = new Article($news_item);
            $article_obj->type_id = 2;
            $article_obj->views = (int)$article_obj->views;
            $article_obj->created_at = Carbon::createFromTimestamp($created_at);
            $article_obj->updated_at = Carbon::createFromTimestamp($updated_at);
            $article_obj->save();
            echo "Added news: " . $news_item['title'] . PHP_EOL;
        }
        foreach ($articles as $article) {
            unset($article['']);
            $created_at = $article['created_at'];
            unset($article['created_at']);
            $updated_at = $article['updated_at'];
            unset($article['updated_at']);
            $article_obj = new Article($article);
            $article_obj->type_id = 1;
            $article_obj->views = (int)$article_obj->views;
            $article_obj->created_at = Carbon::createFromTimestamp($created_at);
            $article_obj->updated_at = Carbon::createFromTimestamp($updated_at);
            $article_obj->save();
            echo "Added article: " . $article['title'] . PHP_EOL;
        }
        foreach ($blog as $blog_item) {
            unset($blog_item['']);
            $created_at = $blog_item['created_at'];
            unset($blog_item['created_at']);
            $updated_at = $blog_item['updated_at'];
            unset($blog_item['updated_at']);
            $article_obj = new Article($blog_item);
            $article_obj->type_id = 8;
            $article_obj->views = (int)$article_obj->views;
            $article_obj->created_at = Carbon::createFromTimestamp($created_at);
            $article_obj->updated_at = Carbon::createFromTimestamp($updated_at);
            $article_obj->save();
            echo "Added blog: ".$blog_item['title'].PHP_EOL;
        }
    }
}
