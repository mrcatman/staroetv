<?php

namespace App\Http\Controllers;

use App\Constants\CacheTimes;
use App\Constants\MaterialTypes;
use App\Crossposting\Services\Twitter\TwitterCrossposter;
use App\Models\Article;
use App\Crossposting\CrossposterManager;
use App\Helpers\PermissionsHelper;
use App\Helpers\StringsHelper;
use App\Helpers\ViewsHelper;
use App\Models\ArticleBinding;
use App\Models\Channel;
use App\Models\Comment;
use App\Models\Crosspost;
use App\Models\Tag;
use App\Models\TagMaterial;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Mews\Purifier\Facades\Purifier;

class ArticlesController extends EntityController {

    protected $entity_class = Article::class;
    protected $permissions = [
        'approve' => 'nwapprove'
    ];

    public function __construct(
        private CrossposterManager $crossposterManager
    ) {
        $this->redirect_after_delete = route('articles.index');
    }

    private function getTags() {
        return Cache::remember('articles_tags', CacheTimes::RELATION, function() {
             return Tag::all()->map(function($tag) {
                $article_ids = TagMaterial::where(['tag_id' => $tag->id, 'material_type' => 'articles'])->pluck('material_id');
                $tag->count = Article::approved()->whereIn('id', $article_ids)->count();
                return $tag;
            })->filter(function ($tag) {
                return $tag->count > 0;
            })->sortByDesc('count')->values();
        });
    }

    public function redirect($conditions) {
        $article = Article::where($conditions)->first();
        if (!$article) {
            $url = "/".request()->path();
            $article = Article::where(['original_url' => $url])->first();
            if (!$article  || ($article->is_approved && !$article->can_edit)) {
                return redirect(route('index'));
            }
            return redirect($article->url);
        }
        if (!$article  || (!$article->is_approved && !$article->can_edit)) {
            return redirect(route('index'));
        }

        return redirect($article->url);
    }

    public function show($url) {
        $data = Cache::remember('article_'.$url, CacheTimes::PAGE,  function() use ($url) {
            $article = Article::where(['url' => $url])->orWhere('id', $url)->firstOrFail();
            $see_also = Article::where('id', '<', $article->id)->approved()->orderBy('created_at', 'desc')->limit(5)->get();
            $see_also = $see_also->merge(
                Article::where('id', '>', $article->id)->approved()->orderBy('id', 'asc')->limit(3)->get()
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
        $can_edit = false;
        $can_approve = false;

        if ($show_actions_panel) {
            $can_edit = $article->can_edit;
            $can_approve = PermissionsHelper::allows('nwapprove');
        }

        return view("pages.articles.show", [
            'show_actions_panel' => $show_actions_panel,
            'article' => $article,
            'see_also' => $see_also,
            'can_edit' => $can_edit,
            'can_approve' => $can_approve
        ]);
    }

    public function canCrosspost() {
        if (PermissionsHelper::isBanned()) {
            return false;
        }
        return auth()->user() && PermissionsHelper::allows('nwcrosspost');
    }

    public function index() {
        $articles = Article::where(function($q) {
            $q->where('type_id', '!=', MaterialTypes::TYPE_BLOG);
            $q->orWhereNull('type_id');
        });

        $can_approve = PermissionsHelper::allows('nwapprove');
        $show_all = true;
        if (!$can_approve || !request()->input('show_all')) {
            $show_all = false;
            $articles = $articles->approved();
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

        $channel = null;
        $tags = [];

        if (request()->has('channel')) {
            $channel = Channel::findByIdOrUrl(request()->input('channel'));
            $articles = $articles->whereHas('bindings', function($q) use ($channel) {
                $q->where(['channel_id' => $channel->id]);
            });
        } else {
            $tags = $this->getTags();
        }

        $articles = $articles->paginate(20);

        $can_add = PermissionsHelper::allows('nwadd');

        $show_actions_panel = auth()->user() && auth()->user()->group_id > 2 && auth()->user()->group_id < 255;
        return view("pages.articles.index", [
            'channel' => $channel,
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
            $can_edit_all = PermissionsHelper::allows('nwedit');
            return view("pages.articles.form", [
                'article' => null,
                'tags' => $this->getTags(),
                'can_edit_all' => $can_edit_all
            ]);
        } else {
            return redirect('/');
        }
    }

    protected function getCrossposts($article) {
        $crossposts = $article->crossposts;
        foreach ($crossposts as $crosspost) {
            $crossposter = $this->crossposterManager->get($crosspost->network);
            $crosspost->link = $crossposter->makeLinks($crosspost->crosspost_id);
        }
        return $crossposts;
    }

    protected function getCrosspostServices() {
        $list = $this->crossposterManager->getList();
        $services = [];
        foreach ($list as $service) {
            if ($service->isActive()) {
                $services[] = [
                    'id' => $service->getParam('id'),
                    'name' => $service->getParam('public_name'),
                    'can_edit_posts' => $service->getParam('can_edit_posts'),
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
        $services = null;
        if (PermissionsHelper::allows('nwcrosspost')) {
            $crossposts = $this->getCrossposts($article);
            $services = $this->getCrosspostServices();
        }

        $can_edit_all = PermissionsHelper::allows('nwedit');
        return view("pages.articles.form", [
            'services' => $services,
            'crossposts' => $crossposts,
            'article' => $article,
            'tags' => $this->getTags(),
            'can_edit_all' => $can_edit_all
        ]);
    }

    public function getCrosspostParameters($article = null, $service_id = null) {
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

        if (!$service_id) {
            $service_id = request()->input('service_id');
        }
        if ($service_id === "twitter") {
            $text = strip_tags($article->short_content);
            $length = TwitterCrossposter::TWEET_LENGTH - TwitterCrossposter::LINK_LENGTH;
            if (mb_strlen($text, "UTF-8") > $length) {
                $text = wordwrap($text, $length - 3);
                $text = substr($text, 0, strpos($text, "\n"));
                $text .= "...";
            }
        } else {
            $text = $article->title . PHP_EOL . PHP_EOL . strip_tags($article->short_content);
        }

        $link = "https://staroetv.su" . $article->url;
        $picture = $service_id === "telegram" ? $article->cover : null;

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
        $crossposter = $this->crossposterManager->get($network_id);
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

            $text = request()->input('text', $parameters['text']);
            $link = request()->input('text', $parameters['link']);
            $media = request()->input('picture', $parameters['picture']);

            $post->setParam('text', $text);
            $post->setParam('link', $link);

            if ($media) {
                $post->setParam('media', ['type' => 'picture', 'picture' => $media]);
            }

            if ($crosspost) {
                $post->setFieldsToUpdate([
                    'text' => $crosspost->text != $text,
                    'link' => $crosspost->link != $link,
                    'media' => [$crosspost->picture != $media]
                ]);
                $crossposter->editPost($crosspost->crosspost_id, $post);
            } else {
                $post_id = $crossposter->createPost($post);
                $crosspost = new Crosspost([
                    'network' => $network_id,
                    'article_id' => $article->id,
                    'crosspost_id' => $post_id,
                    'text' => $text,
                    'picture' => $media,
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
            $article = new Article();

            $article->pending = PermissionsHelper::allows('nwpremod');
            $user = auth()->user();
            $article->username = $user->username;
            $article->user_id = $user->id;
            $article->views = 0;
            $article->slug = StringsHelper::transliterate($article->title);
            $article->original_id = $article->id;
            return $this->fillData($article);

            //$article->month = date('m', time());
            //$article->day = date('d', time());
            //$article->year = date('Y', time());
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
        return $this->fillData($article);
    }

    protected function fillData($article) {
        $rules = [
            'title' => 'required|min:1',
            'content' => 'required|min:1',
            'cover_id' => 'sometimes',
            'short_content' => 'sometimes',
            'source' => 'sometimes',
            'slug' => 'sometimes'
        ];
        if (PermissionsHelper::allows('nwedit') && request()->input('created_at', '') != '') {
            $rules['created_at'] = 'date';
        }
        $data = request()->validate($rules);
        $article->fill($data);
        $article->content = Purifier::clean($article->content);
        $article->source = strip_tags($article->source);

        $this->saveEntity($article);

        $this->setTags($article);
        $this->clearCache($article);

        return [
            'status' => 1,
            'text' => 'Обновлено'
        ];
    }

    private function setTags($article) {
        $tags = json_decode(request()->input('tags'));
        if ($tags != null) {
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
        if ($bindings != null) {
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

    private function clearCache(Article $article)
    {
        Cache::forget('article_'.$article->url);
        Cache::forget('article_short_content_'.$article->id);
        Cache::forget('article_fixed_content_'.$article->id);
        Cache::forget('articles_tags');
    }

    protected function afterDelete(Model $entity)
    {
        $this->clearCache($entity);
        return parent::afterDelete($entity);
    }

}
