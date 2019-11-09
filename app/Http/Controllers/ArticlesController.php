<?php

namespace App\Http\Controllers;


use App\Article;
use App\ArticleCategory;
use App\Crosspost;
use App\Crossposting\CrossposterResolver;
use App\Helpers\PermissionsHelper;
use Illuminate\Support\Facades\URL;

class ArticlesController extends Controller {

    private $types_data = [
        Article::TYPE_NEWS => [
            'title' => "Новости",
            'add_title' => "Предложить новость",
            'edit_title' => "Изменить новость",
            'delete_title' => 'Удалить',
            'add_link' => "/news/add",
            'edit_link' => "/news/edit",
            'permission_add' => 'nwadd',
            'permission_edit' => 'nwoedit',
            'permission_edit_all' => 'nwedit',
            'permission_delete' => 'nwodel',
            'permission_delete_all' => 'nwdel',
        ],
        Article::TYPE_ARTICLES => [
            'title' => "Статьи",
            'add_title' => "Предложить статью",
            'edit_title' => "Изменить статью",
            'delete_title' => 'Удалить',
            'add_link' => "/articles/add",
            'edit_link' => "/articles/edit",
            'permission_add' => 'sfadd',
            'permission_edit' => 'sfoedit',
            'permission_edit_all' => 'sfedit',
            'permission_delete' => 'sfodel',
            'permission_delete_all' => 'sfdel',
        ],
        Article::TYPE_BLOG => [
            'title' => "Блог",
            'add_title' => "Сделать запись в блоге",
            'edit_title' => "Изменить запись в блоге",
            'delete_title' => 'Удалить',
            'add_link' => "/blog/add",
            'edit_link' => "/blog/edit",
            'permission_add' => 'bladd',
            'permission_edit' => 'bloedit',
            'permission_edit_all' => 'bledit',
            'permission_delete' => 'blodel',
            'permission_delete_all' => 'bldel',

        ]
    ];

    public function show($conditions) {
        $article = Article::where($conditions)->where(['pending' => false])->first();
        if (!$article) {
            return redirect("/");
        }
        $see_also = Article::where('id', '<', $article->id)->where(['pending' => 0, 'type_id' => $conditions['type_id']])->orderBy('id', 'desc')->limit(5)->get();
        $see_also = $see_also->merge(
            Article::where('id', '>', $article->id)->where(['pending' => 0, 'type_id' => $conditions['type_id']])->orderBy('id', 'asc')->limit(3)->get()
        );
        $article->views++;
        $article->save();

        $can_edit = $this->canEdit($article);
        $can_delete = $this->canDelete($article);
        $edit_link = $this->types_data[$article->type_id]['edit_link']."/".$article->original_id;
        $edit_title = $this->types_data[$article->type_id]['edit_title'];
        $delete_title = $this->types_data[$article->type_id]['delete_title'];
        return view("pages.article", [
            'edit_link' => $edit_link,
            'edit_title' => $edit_title,
            'delete_title' => $delete_title,
            'can_edit' => $can_edit,
            'can_delete' => $can_delete,
            'article' => $article,
            'see_also' => $see_also
        ]);
    }

    public function canEdit($article) {
        $type_data = $this->types_data[$article->type_id];
        return auth()->user() && (auth()->user()->id === $article->user_id && PermissionsHelper::allows($type_data['permission_edit'])) || PermissionsHelper::allows($type_data['permission_edit_all']);
    }

    public function canDelete($article) {
        $type_data = $this->types_data[$article->type_id];
        return auth()->user() && (auth()->user()->id === $article->user_id && PermissionsHelper::allows($type_data['permission_delete'])) || PermissionsHelper::allows($type_data['permission_delete_all']);
    }


    public function list($conditions) {
        $category = null;
        if (isset($conditions['category_id'])) {
            $category = ArticleCategory::where(['id' => $conditions['category_id']])->first();
            if (!$category) {
                $category = ArticleCategory::where(['url' => $conditions['category_id']])->first();
            }
           $conditions['category_id'] = $category->original_id;
        }
        $articles = Article::where($conditions)->where(['pending' => false])->orderBy('id', 'desc')->paginate(20);
        $categories = ArticleCategory::where(['type_id' => $conditions['type_id']])->get();
        $base_url = "/".Article::names[$conditions['type_id']];

        $title = $this->types_data[$conditions['type_id']]['title'];
        $add_title = $this->types_data[$conditions['type_id']]['add_title'];
        $permission_id = $this->types_data[$conditions['type_id']]['permission_add'];
        $add_link = $this->types_data[$conditions['type_id']]['add_link'];

        $can_add = PermissionsHelper::allows($permission_id);

        return view("pages.articles", [
            'title' => $title,
            'add_title' => $add_title,
            'can_add' => $can_add,
            'add_link' => $add_link,
            'category' => $category,
            'base_url' => $base_url,
            'categories' => $categories,
            'articles' => $articles,
        ]);
    }

    public function add($conditions) {
        $type_id = $conditions['type_id'];
        $permission_id = $this->types_data[$type_id]['permission_add'];
        if (PermissionsHelper::allows($permission_id)) {
            $add_title = $this->types_data[$conditions['type_id']]['add_title'];
            $categories = ArticleCategory::where(['type_id' => $type_id])->get();
            return view("pages.forms.articles", [
                'categories' => $categories,
                'title' => $add_title,
                'article' => null,
                'type_id' => $type_id
            ]);
        } else {
            return view('pages.errors.403');
        }
    }

    protected function getCrossposts($article) {
        $resolver = new CrossposterResolver();
        $crossposts = $article->crossposts;
        foreach ($crossposts as $crosspost) {
            $crossposter = $resolver->get($crosspost->network);
            $crosspost->link = $crossposter->makeLinkById($crosspost->crosspost_id);
        }
        return $crossposts;
    }

    protected function getCrosspostNetworks() {
        $resolver = new CrossposterResolver();
        $list = $resolver->getList();
        $services = [];
        foreach ($list as $service_name) {
            $service = new $service_name;
            if ($service->isActive()) {
                $services[] = [
                    'id' => $service->id,
                    'name' => $service->public_name,
                ];
            }
        }
        return collect($services);
    }

    public function edit($conditions) {
        $article = Article::where($conditions)->first();
        if (!$article) {
            return redirect("/");
        }
        $can_edit = $this->canEdit($article);
        if (!$can_edit || ($article->pending && $article->user_id != auth()->user()->id)) {
            return view('pages.errors.403');
        }
        $type_id = $conditions['type_id'];
        $edit_title = $this->types_data[$type_id]['edit_title'];
        $categories = ArticleCategory::where(['type_id' => $type_id])->get();
        $crossposts = $this->getCrossposts($article);
        $networks = $this->getCrosspostNetworks();
        return view("pages.forms.articles", [
            'networks' => $networks,
            'crossposts' => $crossposts,
            'categories' => $categories,
            'title' => $edit_title,
            'article' => $article,
            'type_id' => $type_id
        ]);
    }

    public function crosspost() {
        $article = Article::find(request()->input('article_id'));
        if (!$article) {
            return [
                'status' => 0,
                'text' => 'Статья не найдена'
            ];
        }
        $can_edit = $this->canEdit($article);
        if (!$can_edit) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }
        $network_id = request()->input('network_id');
        $crossposter = (new CrossposterResolver())->get($network_id);
        if (!$crossposter) {
            return [
                'status' => 0,
                'text' => 'Ошибка: кросспостер не найден'
            ];
        }
        $post = $crossposter->getPostInstance();

        $link = "http://staroetv.su/".$article->url;
        $text = $article->title.PHP_EOL.PHP_EOL.$article->short_content;
        $post->setText($text)->setLink($link);

        $picture = $article->cover;
        if ($picture) {
            $post->setPicture($picture);
        }

        $crosspost = Crosspost::where(['article_id' => $article->id, 'network' => $network_id])->first();
        if ($crosspost) {
            if ($crosspost->text == $text) {
                $post->doNotChangeText();
            }
            if ($crosspost->link == $link) {
                $post->doNotChangeLink();
            }
            if ($crosspost->picture == $picture) {
                $post->doNotChangePicture();
            }
            $crossposter->editPost($crosspost->crosspost_id, $post);
        } else {
            $post_id = $crossposter->createPost($post);
            $crosspost = new Crosspost([
                'network' => $network_id,
                'article_id' => $article->id,
                'crosspost_id' => $post_id,
                'text' => $text,
                'picture' => $picture,
                'link' => $link
            ]);
            $crosspost->save();
        }
        $link = $crossposter->makeLinkById($crosspost->crosspost_id);
        return [
            'status' => 1,
            'text' => 'Пост сделан',
            'data' => [
                'link' => $link,
                'crosspost' => $crosspost,
            ]
        ];
    }

    public function update($id) {
        $article = Article::find($id);
        if (!$article) {
            return [
                'status' => 0,
                'text' => 'Статья не найдена'
            ];
        }
        $can_edit = $this->canEdit($article);
        if (!$can_edit) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }
        $data = request()->validate([
            'category_id' => 'required',
            'title' => 'required|min:1',
            'content' => 'required|min:1',
            'cover_id' => 'sometimes',
            'short_content' => 'sometimes',
            'source' => 'sometimes'
        ]);
        $article->fill($data);
        $article->save();
        return [
            'status' => 1,
            'text' => 'Обновлено'
        ];
    }
}
