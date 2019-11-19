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
            'approve_title' => 'Одобрить новость',
            'unapprove_title' => 'Скрыть новость',
            'delete_title' => 'Удалить',
            'base_link' => '/news',
            'add_link' => "/news/add",
            'edit_link' => "/news/edit",
            'permission_add' => 'nwadd',
            'permission_edit' => 'nwoedit',
            'permission_edit_all' => 'nwedit',
            'permission_delete' => 'nwodel',
            'permission_delete_all' => 'nwdel',
            'permission_crosspost' => 'nwcrosspost',
            'permission_premod' => 'nwpremod',
            'permission_approve' => 'nwapprove',
        ],
        Article::TYPE_ARTICLES => [
            'title' => "Статьи",
            'add_title' => "Предложить статью",
            'edit_title' => "Изменить статью",
            'approve_title' => 'Одобрить статью',
            'unapprove_title' => 'Скрыть статью',
            'delete_title' => 'Удалить',
            'base_link' => '/articles',
            'add_link' => "/articles/add",
            'edit_link' => "/articles/edit",
            'permission_add' => 'sfadd',
            'permission_edit' => 'sfoedit',
            'permission_edit_all' => 'sfedit',
            'permission_delete' => 'sfodel',
            'permission_delete_all' => 'sfdel',
            'permission_crosspost' => 'sfcrosspost',
            'permission_premod' => 'sfpremod',
            'permission_approve' => 'sfapprove',
        ],
        Article::TYPE_BLOG => [
            'title' => "Блог",
            'add_title' => "Сделать запись в блоге",
            'edit_title' => "Изменить запись в блоге",
            'approve_title' => 'Одобрить запись',
            'unapprove_title' => 'Скрыть запись',
            'delete_title' => 'Удалить',
            'base_link' => '/blog',
            'add_link' => "/blog/add",
            'edit_link' => "/blog/edit",
            'permission_add' => 'bladd',
            'permission_edit' => 'bloedit',
            'permission_edit_all' => 'bledit',
            'permission_delete' => 'blodel',
            'permission_delete_all' => 'bldel',
            'permission_crosspost' => 'blcrosspost',
            'permission_premod' => 'blpremod',
            'permission_approve' => 'blapprove',
        ]
    ];

    public function show($conditions) {
        $article = Article::where($conditions)->first();
        if (!$article || ($article->pending && !$this->canEdit($article))) {
            return redirect("/");
        }
        $see_also = Article::where('id', '<', $article->id)->where(['pending' => 0, 'type_id' => $conditions['type_id']])->orderBy('id', 'desc')->limit(5)->get();
        $see_also = $see_also->merge(
            Article::where('id', '>', $article->id)->where(['pending' => 0, 'type_id' => $conditions['type_id']])->orderBy('id', 'asc')->limit(3)->get()
        );
        //$article->views++;
        $article->save();

        $can_edit = $this->canEdit($article);
        $can_delete = $this->canDelete($article);
        $can_approve = PermissionsHelper::allows($this->types_data[$article->type_id]['permission_approve']);

        $edit_link = $this->types_data[$article->type_id]['edit_link']."/".$article->original_id;
        $edit_title = $this->types_data[$article->type_id]['edit_title'];
        $delete_title = $this->types_data[$article->type_id]['delete_title'];
        $key = $article->pending ? 'approve_title' : 'unapprove_title';
        $approve_title = $this->types_data[$article->type_id][$key];
        return view("pages.article", [
            'edit_link' => $edit_link,
            'edit_title' => $edit_title,
            'approve_title' => $approve_title,
            'delete_title' => $delete_title,
            'can_edit' => $can_edit,
            'can_delete' => $can_delete,
            'can_approve' => $can_approve,
            'article' => $article,
            'see_also' => $see_also
        ]);
    }

    public function approve() {
        $article = Article::find(request()->input('article_id'));
        if (!$article) {
            return [
                'status' => 0,
                'text' => 'Статья не найдена'
            ];
        }
        $can_approve = PermissionsHelper::allows($this->types_data[$article->type_id]['permission_approve']);
        if ($can_approve) {
            $status = request()->input('status', !$article->pending);
            $article->pending = $status;
            $article->save();
        }
        return [
            'status' => 1,
            'text' => 'Обновлено',
            'redirect_to' => $article->url
        ];
    }

    public function delete() {
        $article = Article::find(request()->input('article_id'));
        if (!$article) {
            return [
                'status' => 0,
                'text' => 'Статья не найдена'
            ];
        }
        $redirect_to = $this->types_data[$article->type_id]['base_link'];
        $can_delete = $this->canDelete($article);
        if ($can_delete) {
            $article->delete();
        }
        return [
            'status' => 1,
            'text' => 'Удалено',
            'redirect_to' => $redirect_to
        ];
    }

    public function canEdit($article) {
        if (PermissionsHelper::isBanned()) {
            return false;
        }
        $type_data = $this->types_data[$article->type_id];
        return auth()->user() && (auth()->user()->id === $article->user_id && PermissionsHelper::allows($type_data['permission_edit'])) || PermissionsHelper::allows($type_data['permission_edit_all']);
    }

    public function canCrosspost($article) {
        if (PermissionsHelper::isBanned()) {
            return false;
        }
        $type_data = $this->types_data[$article->type_id];
        return auth()->user() && PermissionsHelper::allows($type_data['permission_crosspost']);
    }


    public function canDelete($article) {
        if (PermissionsHelper::isBanned()) {
            return false;
        }
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
        if (PermissionsHelper::isBanned()) {
            return view('pages.errors.banned');
        }
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
                    'can_edit_posts' => $service->can_edit_posts
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

        $crosspost_permission = $this->types_data[$type_id]['permission_crosspost'];
        $crossposts = null;
        $networks = null;
        if (PermissionsHelper::allows($crosspost_permission)) {
            $crossposts = $this->getCrossposts($article);
            $networks = $this->getCrosspostNetworks();
        }
        return view("pages.forms.articles", [
            'networks' => $networks,
            'crossposts' => $crossposts,
            'categories' => $categories,
            'title' => $edit_title,
            'article' => $article,
            'type_id' => $type_id
        ]);
    }

    public function getCrosspostParameters($article = null, $network_id = null) {
        if (!$article) {
            $article = Article::find(request()->input('article_id'));
            if (!$article) {
                return [
                    'status' => 0,
                    'text' => 'Статья не найдена'
                ];
            }
            $can_crosspost = $this->canCrosspost($article);
            if (!$can_crosspost) {
                return [
                    'status' => 0,
                    'text' => 'Ошибка доступа'
                ];
            }
        }
        if (!$network_id) {
            $network_id = request()->input('network_id');
        }
        if ($network_id === "twitter") {
            $text = strip_tags($article->short_content);
            $length = 280 - 23;
            if (mb_strlen($text, "UTF-8") > $length) {
                $text = wordwrap($text, $length - 3);
                $text = substr($text, 0, strpos($text, "\n"));
                $text .= "...";
            }
        } else {
            $text = $article->title . PHP_EOL . PHP_EOL . strip_tags($article->short_content);
        }

        $link = "http://staroetv.mrcatmann.ru" . $article->url;
        if ($network_id === "telegram") {
            $picture = $article->cover;
        } else {
            $picture = null;
        }
        return [
            'status' => 1,
            'data' => [
                'text' => $text,
                'link' => $link,
                'picture' => $picture
            ]
        ];
    }


    public function crosspost() {
        $article = Article::find(request()->input('article_id'));
        if (!$article) {
            return [
                'status' => 0,
                'text' => 'Статья не найдена'
            ];
        }
        $can_crosspost = $this->canCrosspost($article);
        if (!$can_crosspost) {
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
        $crosspost = Crosspost::where(['article_id' => $article->id, 'network' => $network_id])->first();
        if (request()->input('delete', false)) {
            if (!$crosspost) {
                return [
                    'status' => 0,
                    'text' => 'Ошибка: не найден пост в данной соцсети'
                ];
            }
            $crossposter->deletePost($crosspost->crosspost_id);
            $crosspost->delete();
            return [
                'status' => 1,
                'text' => 'Пост удален',
            ];
        } else {

            $post = $crossposter->getPostInstance();
            $parameters = $this->getCrosspostParameters($article, $network_id)['data'];

            $text = $parameters['text'];
            if (request()->has('text')) {
                $text = request()->input('text');
            }
            $post->setText($text);

            $link = $parameters['link'];
            if (request()->has('link')) {
                $link = request()->input('link');
            }
            $post->setLink($link);

            $picture = $parameters['picture'];
            if (request()->has('picture')) {
                $picture = request()->input('picture');
            }
            $post->setPicture($picture);

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
    }

    protected function transliterate($st) {
        $st = mb_strtolower($st, "UTF-8");
        $st = str_replace("_", "", $st);
        $st = strtr($st,
            "абвгдежзийклмнопрстуфыэАБВГДЕЖЗИЙКЛМНОПРСТУФЫЭ",
            "abvgdegziyklmnoprstufieABVGDEGZIYKLMNOPRSTUFIE"
        );
        $st = strtr($st, array(
            'ё'=>"yo",    'х'=>"h",  'ц'=>"ts",  'ч'=>"ch", 'ш'=>"sh",
            'щ'=>"shch",  'ъ'=>'',   'ь'=>'',    'ю'=>"yu", 'я'=>"ya",
            'Ё'=>"Yo",    'Х'=>"H",  'Ц'=>"Ts",  'Ч'=>"Ch", 'Ш'=>"Sh",
            'Щ'=>"Shch",  'Ъ'=>'',   'Ь'=>'',    'Ю'=>"Yu", 'Я'=>"Ya",
        ));
        return $st;
    }

    public function save() {
        $type_id = request()->input('type_id');
        $permission_id = $this->types_data[$type_id]['permission_add'];
        if (PermissionsHelper::allows($permission_id) && !PermissionsHelper::isBanned()) {
            $data = request()->validate([
                'category_id' => 'required',
                'title' => 'required|min:1',
                'content' => 'required|min:1',
                'cover_id' => 'sometimes',
                'short_content' => 'sometimes',
                'source' => 'sometimes',
                'type_id' => 'required'
            ]);
            $original_id = Article::where(['type_id' => $type_id])->max('original_id') + 1;
            $article = new Article($data);
            $article->original_id = $original_id;
            $premod_permission_id = $this->types_data[$type_id]['permission_premod'];
            $need_premod = PermissionsHelper::allows($premod_permission_id);
            $article->pending = !!$need_premod;
            $user = auth()->user();
            $article->username = $user->username;
            $article->user_id = $user->id;
            $article->views = 0;
            $article->path = $this->transliterate($article->title);

            //$article->month = date('m', time());
            //$article->day = date('d', time());
            //$article->year = date('Y', time());

            $article->save();
            $article_link = $article->url;
            return [
                'status' => 1,
                'text' => 'Добавлено',
                'redirect_to' => $article_link
            ];
        }
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
