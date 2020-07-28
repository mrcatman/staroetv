<?php

namespace App\Console\Commands;

use App\Article;
use App\ArticleCategory;
use App\Helpers\CSVHelper;
use App\UserAward;
use App\UserReputation;
use Carbon\Carbon;
use Illuminate\Console\Command;

class importArticleCategories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'categories:import';

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
        $articles_categories = CSVHelper::transform(public_path("data/bl_bl.csv"), [
           'original_id', '', '', 'title', '', 'url'
        ], false);
        foreach ($articles_categories as &$category) {
            $category['type_id'] = Article::TYPE_ARTICLES;
        }
        $blog_categories = CSVHelper::transform(public_path("data/sf_sf.csv"), [
            'original_id', '', '', '', '', 'title', '', '', '', '', 'url'
        ], false);
        foreach ($blog_categories as &$category) {
            $category['type_id'] = Article::TYPE_BLOG;
        }
        $news_categories = CSVHelper::transform(public_path("data/nw_nw.csv"), [
            'original_id', '', '', 'title', '', 'url'
        ], false);
        foreach ($news_categories as &$category) {
            $category['type_id'] = Article::TYPE_NEWS;
        }
        $categories = array_merge($articles_categories, $blog_categories, $news_categories);
        file_put_contents(public_path("data/categories.txt"), json_encode($categories, JSON_UNESCAPED_UNICODE));
        foreach ($categories as $category_item) {
            unset($category_item['']);
            $article_category_obj = new ArticleCategory($category_item);
            $article_category_obj->save();
            echo "Added category: " . $category_item['title'] . PHP_EOL;
        }

    }
}
