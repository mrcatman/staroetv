<?php

namespace App\Http\Controllers;

use App\Constants\MaterialTypes;
use App\Models\Article;
use App\Crossposting\CrossposterManager;
use App\Helpers\PermissionsHelper;
use App\Helpers\StringsHelper;
use App\Helpers\ViewsHelper;
use App\Models\ArticleBinding;
use App\Models\Comment;
use App\Models\Crosspost;
use App\Models\Tag;
use App\Models\TagMaterial;
use Illuminate\Support\Facades\Cache;

class ArticlesController extends EntityController {

    protected $entity_class = Article::class;
    protected $permissions = [
        'approve' => 'nwapprove'
    ];
    protected $redirect_after_delete = '/articles';

    public $types_data = [
        MaterialTypes::TYPE_NEWS => [
            'title' => "Новости",
            'add_title' => "Предложить новость",
            'edit_title' => "Изменить новость",
            'approve_title' => 'Одобрить новость',
            'unapprove_title' => 'Скрыть новость',
            'delete_title' => 'Удалить',
            'base_url' => '/news',
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
        MaterialTypes::TYPE_ARTICLES => [
            'title' => "Статьи",
            'add_title' => "Предложить статью",
            'edit_title' => "Изменить статью",
            'approve_title' => 'Одобрить статью',
            'unapprove_title' => 'Скрыть статью',
            'delete_title' => 'Удалить',
            'base_url' => '/articles',
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
        MaterialTypes::TYPE_BLOG => [
            'title' => "Блог",
            'add_title' => "Сделать запись в блоге",
            'edit_title' => "Изменить запись в блоге",
            'approve_title' => 'Одобрить запись',
            'unapprove_title' => 'Скрыть запись',
            'delete_title' => 'Удалить',
            'base_url' => '/blog',
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

    public function redirect($conditions) {
        $article = Article::where($conditions)->first();
        if (!$article) {
            $url = "/".request()->path();
            $article = Article::where(['original_url' => $url])->first();
            if (!$article  || ($article->pending && !$article->can_edit)) {
                return redirect(route('index'));
            }
            return redirect($article->url);
        }
        if (!$article  || ($article->pending && !$article->can_edit)) {
            return redirect(route('index'));
        }

        return redirect($article->url);
    }

    public function show($url) {
        $data = Cache::remember('article_'.$url, 60 * 10,  function() use ($url) {
            $article = Article::where(['url' => $url])->firstOrFail();
            $see_also = Article::where('id', '<', $article->id)->where(['pending' => false])->orderBy('created_at', 'desc')->limit(5)->get();
            $see_also = $see_also->merge(
                Article::where('id', '>', $article->id)->where(['pending' => false])->orderBy('id', 'asc')->limit(3)->get()
            );
            return [
                'article' => $article,
                'see_also' => $see_also
            ];
        });

        $article = $data['article'];

        if ($article->id == 2006) {
            return redirect(route('contact.digitization.index'));
        }

        $see_also = $data['see_also'];

        ViewsHelper::increment($article, 'articles');
        $show_actions_panel = auth()->user() && auth()->user()->group_id > 2 && auth()->user()->group_id < 255;

        return view("pages.articles.show", [
            'show_actions_panel' => $show_actions_panel,
            'article' => $article,
            'see_also' => $see_also
        ]);
    }

    public function canCrosspost() {
        if (PermissionsHelper::isBanned()) {
            return false;
        }
        return auth()->user() && PermissionsHelper::allows('nwcrosspost');
    }

    public function index() {
        $category = null;
        $articles = Article::where(function($q) {
            $q->where('type_id', '!=', MaterialTypes::TYPE_BLOG);
            $q->orWhereNull('type_id');
        });

        $can_approve = PermissionsHelper::allows('nwapprove');
        $show_all = true;
        if (!$can_approve || !request()->input('show_all')) {
            $show_all = false;
            $articles = $articles->where(['pending' => false]);
        }
        $search = null;
        $articles = $articles->orderBy('created_at', 'desc');
        if (request()->has('search')) {
            $search = request()->input('search');
            $articles = $articles->where(function($q) use ($search) {
                $q->where('title', 'LIKE', '%'. $search .'%');
                $q->orWhere('content', 'LIKE', '%'. $search.'%');
            });
        }
        $tag = null;
        if (request()->has('tag')) {
            $tag = Tag::where(['url' => request()->input('tag')])->first();
            if ($tag) {
                $ids = TagMaterial::where(['tag_id' => $tag->id, 'material_type' => 'articles'])->pluck('material_id');
                $articles = $articles->whereIn('id', $ids);
            }

        }
        $articles = $articles->paginate(20);

        $tags = Cache::remember('articles_tags', 60 * 30, function() {
            $all_ids = Article::where(['pending' => false])->where('type_id', '!=', MaterialTypes::TYPE_BLOG)->pluck('id');
            return Tag::all()->map(function($tag) use ($all_ids) {
                $count = TagMaterial::where(['tag_id' => $tag->id, 'material_type' => 'articles'])->whereIn('material_id', $all_ids)->count();
                $tag->count = $count;
                return $tag;
            })->filter(function ($tag) {
                return $tag->count > 0;
            })->sortByDesc('count');
        });


        $can_add = PermissionsHelper::allows('nwadd');

        $show_actions_panel = auth()->user() && auth()->user()->group_id > 2 && auth()->user()->group_id < 255;
        return view("pages.articles.index", [
            'tag' => $tag,
            'tags' => $tags,
            'search' => $search,
            'show_all' => $show_all,
            'show_actions_panel' => $show_actions_panel,
            'can_approve' => $can_approve,
            'can_add' => $can_add,
            'articles' => $articles,
        ]);
    }

    public function add() {
        if (!PermissionsHelper::isBanned() && PermissionsHelper::allows('nwadd')) {
            return view("pages.articles.form", [
                'article' => null,
            ]);
        } else {
            return view('pages.errors.403');
        }
    }

    protected function getCrossposts($article) {
        $resolver = new CrossposterManager();
        $crossposts = $article->crossposts;
        foreach ($crossposts as $crosspost) {
            $crossposter = $resolver->get($crosspost->network);
            $crosspost->link = $crossposter->makeLinks($crosspost->crosspost_id);
        }
        return $crossposts;
    }

    protected function getCrosspostNetworks() {
        $resolver = new CrossposterManager();
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


    public function edit($id) {
        $article = Article::find($id);
        if (!$article || !$article->can_edit) {
            return redirect(route('index'));
        }

        $crossposts = null;
        $networks = null;
        if (PermissionsHelper::allows('nwcrosspost')) {
            $crossposts = $this->getCrossposts($article);
            $networks = $this->getCrosspostNetworks();
        }
        return view("pages.articles.form", [
            'networks' => $networks,
            'crossposts' => $crossposts,
            'article' => $article,
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
            $can_crosspost = $this->canCrosspost();
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

        $link = "http://staroetv.su" . $article->url;
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
        $can_crosspost = $this->canCrosspost();
        if (!$can_crosspost) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }
        $network_id = request()->input('network_id');
        $crossposter = (new CrossposterManager())->get($network_id);
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

            $media = null;
            $picture = $parameters['picture'];
            if (request()->has('picture')) {
                $picture = request()->input('picture');
            }
            if ($picture) {
                $media = ['type' => 'picture', 'picture' => $picture];
                $post->setMedia($media);
            }
            if ($crosspost) {
                $post->setFieldsToUpdate([
                    'text' => $crosspost->text != $text,
                    'link' => $crosspost->link != $link,
                    'media' => [$crosspost->picture != $picture]
                ]);
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
            $link = $crossposter->makeLinks($crosspost->crosspost_id);
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



    public function save() {
        if (PermissionsHelper::allows('nwadd') && !PermissionsHelper::isBanned()) {
            $data = request()->validate([
                'title' => 'required|min:1',
                'content' => 'required|min:1',
                'cover_id' => 'sometimes',
                'short_content' => 'sometimes',
                'source' => 'sometimes',
                'slug' => 'sometimes'
            ]);
           // $type_id = Article::TYPE_ARTICLES;
            $article = new Article($data);
            $need_premod = PermissionsHelper::allows('nwpremod');
            $article->pending = !!$need_premod;
            $user = auth()->user();
            $article->username = $user->username;
            $article->user_id = $user->id;
            $article->views = 0;
            $article->slug = StringsHelper::transliterate($article->title);


            //$article->month = date('m', time());
            //$article->day = date('d', time());
            //$article->year = date('Y', time());

            $this->saveEntity($article);

            $article->original_id = $article->id;
            $article->save();
            $article_link = $article->url;
            $this->setTags($article);

            Cache::forget('article_'.$article->url);
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

        if (!$article->can_edit) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }
        $data = request()->validate([
            'title' => 'required|min:1',
            'content' => 'required|min:1',
            'cover_id' => 'sometimes',
            'short_content' => 'sometimes',
            'source' => 'sometimes',
            'slug' => 'sometimes'
        ]);
        $article->fill($data);
        $this->saveEntity($article);

        $this->setTags($article);
        return [
            'status' => 1,
            'text' => 'Обновлено'
        ];
    }

    public function setTags($article) {
        $tags = json_decode(request()->input('tags'));
        if ($tags) {
            $ids = array_map(function($tag) {
                if (!isset($tag->id)) {
                    $new_tag = Tag::where(['name' => $tag->text])->first();
                    if (!$new_tag) {
                        $new_tag = new Tag([
                            'name' => $tag->text,
                            'url' => StringsHelper::transliterate($tag->text)
                        ]);
                        $new_tag->save();
                    }
                    return $new_tag->id;
                }
                return $tag->id;
            }, $tags);
            $article_tags = $article->tags;
            $article_tag_ids = $article_tags->pluck('id');
            foreach ($ids as $tag_id) {
                if (!$article_tag_ids->contains($tag_id)) {
                    $article_tag = new TagMaterial([
                        'tag_id' => $tag_id,
                        'material_id' => $article->id,
                        'material_type' => 'articles'
                    ]);
                    $article_tag->save();
                }
            }
            foreach ($article_tags as $article_tag) {
                if (!in_array($article_tag->id, $ids)) {
                    $article_tag->delete();
                };
            }
        }
        $bindings = json_decode(request()->input('bindings'));
        if ($bindings) {
            $program_ids = $bindings->programs;
            foreach ($program_ids as $program_id) {
                $binding = ArticleBinding::firstOrNew([
                    'article_id' => $article->id,
                    'program_id' => $program_id
                ]);
                $binding->save();
            }
            ArticleBinding::where(['article_id' => $article->id])->where(function($q) use ($program_ids) {
                $q->whereNotNull('program_id');
                $q->whereNotIn('program_id', $program_ids);
            })->delete();
            $channel_ids = $bindings->channels;
            foreach ($channel_ids as $channel_id) {
                $binding = ArticleBinding::firstOrNew([
                    'article_id' => $article->id,
                    'channel_id' => $channel_id
                ]);
                $binding->save();
            }
            ArticleBinding::where(['article_id' => $article->id])->where(function($q) use ($channel_ids) {
                $q->whereNotNull('channel_id');
                $q->whereNotIn('channel_id', $channel_ids);
            })->delete();
        }
    }

    public function getActions() {
        $article = Article::find(request()->input('id'));
        if (!$article) {
            return [
                'status' => 0,
                'text' => 'Не найдено'
            ];
        }

        $can_edit = $article->can_edit;
        $can_approve = PermissionsHelper::allows('nwapprove');
        $selector = "#actions_list_".$article->id;
        return [
            'status' => 1,
            'data' => [
                'dom' => [
                    [
                        'replace' => $selector,
                        'html' => view("blocks/articles.actions", [
                            'article' => $article,
                            'can_edit' => $can_edit,
                            'can_approve' => $can_approve,
                        ])->render()
                    ]
                ]
            ]
        ];
    }

    public function changeType(){
        $article = Article::find(request()->input('id'));
        if (!$article) {
            return [
                'status' => 0,
                'text' => 'Не найдено'
            ];
        }

        if ($article->can_edit) {
            if (!$article->original_url) {
                $article->original_url = $article->url;
            }
            $type_id = $article->type_id;
            $original_id = $article->original_id;
            $article->type_id = request()->input('type_id', 2);
            $article->original_id = Article::where(['type_id' => $article->type_id])->max('original_id') + 1;
            Comment::where(['material_type' => $type_id, 'material_id' => $original_id])->update([
                'material_type' => $article->type_id,
                'material_id' => $article->original_id
            ]);
            $article->save();
            return [
                'status' => 1,
            ];
        } else {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }
    }



}
