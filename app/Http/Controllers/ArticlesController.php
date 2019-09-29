<?php

namespace App\Http\Controllers;


use App\Article;

class ArticlesController extends Controller {

    public function show($conditions) {
        $article = Article::where($conditions)->first();
        $see_also = Article::where('id', '<', $article->id)->where(['pending' => 0, 'type_id' => $conditions['type_id']])->orderBy('id', 'desc')->limit(5)->get();
        $see_also = $see_also->merge(
            Article::where('id', '>', $article->id)->where(['pending' => 0, 'type_id' => $conditions['type_id']])->orderBy('id', 'asc')->limit(3)->get()
        );
        return view("pages.article", [
            'article' => $article,
            'see_also' => $see_also
        ]);
    }


    public function list($conditions) {
        $articles = Article::where($conditions)->orderBy('id', 'desc')->paginate(20);
        return view("pages.articles", [
            'articles' => $articles,
        ]);
    }
}
