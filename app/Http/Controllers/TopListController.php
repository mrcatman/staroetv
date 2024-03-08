<?php

namespace App\Http\Controllers;

use App\Article;
use App\Award;
use App\Channel;
use App\ChannelName;
use App\Comment;
use App\CommentRating;
use App\ForumMessage;
use App\Helpers\BBCodesHelper;
use App\Helpers\CommentsHelper;
use App\Helpers\PermissionsHelper;
use App\Notifications\NewCommentReply;
use App\Notifications\NewForumReply;
use App\Program;
use App\Record;
use App\User;
use App\UserReputation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TopListController extends Controller {

    public $links = [
        'videos' => 'Видеоархив',
        'radio-recordings' => 'Радиоархив',
        'news' => 'Новости',
        'articles' => 'Статьи',
        'forum' => 'Форум',
        'comments' => 'Комментарии',
        'awards' => 'Награды',
        'reputation' => 'Репутация',
    ];

    protected function pluralize($value, $texts) {
        $text = $texts[1];
        if ($value % 10 === 1 && $value !== 11) {
            $text = $texts[0];
        }
        if ($value % 100 === 11 || $value % 10 > 4 || $value % 10 === 0) {
            $text = $texts[2];
        }
        return $text;
    }

    public function reputation() {
        $reputation = UserReputation::select(DB::raw('to_id, sum(weight) as sum_weight'))->groupBy('to_id')->orderBy('sum_weight', 'desc')->limit(10000)->get();
        $list = [];
        foreach ($reputation as $reputation_item) {
            $user = User::find($reputation_item['to_id']);
            if ($user) {
                $list[] = [
                    'user' => $user,
                    'text' => 'Репутация: ',
                    'value' => $reputation_item['sum_weight'],
                    'after_text'=> '',
                ];
            }
        }
        return view('pages.top-list.index', [
            'links' => $this->links,
            'list' => $list
        ]);
    }

    public function videos() {
        $videos = Record::where(['is_radio' => false])->select(DB::raw('author_id, count(author_id) as sum_count'))->groupBy('author_id')->orderBy('sum_count', 'desc')->limit(100)->get();
        $list = [];
        foreach ($videos as $videos_count) {
            $user = User::find($videos_count['author_id']);
            if ($user) {
                $list[] = [
                    'user' => $user,
                    'text' => 'добавил(а) ',
                    'value' => $videos_count['sum_count'],
                    'after_text'=> $this->pluralize($videos_count['sum_count'], ['ролик', 'ролика', 'роликов']),
                ];
            }
        }
        return view('pages.top-list.index', [
            'links' => $this->links,
            'list' => $list
        ]);
    }

    public function radioRecordings() {
        $radio_recordings = Record::where(['is_radio' => true])->select(DB::raw('author_id, count(author_id) as sum_count'))->groupBy('author_id')->orderBy('sum_count', 'desc')->limit(100)->get();
        $list = [];
        foreach ($radio_recordings as $radio_recordings_count) {
            $user = User::find($radio_recordings_count['author_id']);
            if ($user) {
                $list[] = [
                    'user' => $user,
                    'text' => 'добавил(а) ',
                    'value' => $radio_recordings_count['sum_count'],
                    'after_text'=> $this->pluralize($radio_recordings_count['sum_count'], ['запись', 'записи', 'записей']),
                ];
            }
        }
        return view('pages.top-list.index', [
            'links' => $this->links,
            'list' => $list
        ]);
    }

    public function news() {
        $news = Article::where(['type_id' => Article::TYPE_NEWS])->select(DB::raw('user_id, count(user_id) as sum_count'))->groupBy('user_id')->orderBy('sum_count', 'desc')->limit(100)->get();
        $list = [];
        foreach ($news as $news_count) {
            $user = User::find($news_count['user_id']);
            if ($user) {
                $list[] = [
                    'user' => $user,
                    'text' => 'добавил(а) ',
                    'value' => $news_count['sum_count'],
                    'after_text'=> $this->pluralize($news_count['sum_count'], ['новость', 'новости', 'новостей']),
                ];
            }
        }
        return view('pages.top-list.index', [
            'links' => $this->links,
            'list' => $list
        ]);
    }

    public function articles() {
        $news = Article::where(['type_id' => Article::TYPE_ARTICLES])->select(DB::raw('user_id, count(user_id) as sum_count'))->groupBy('user_id')->orderBy('sum_count', 'desc')->limit(100)->get();
        $list = [];
        foreach ($news as $news_count) {
            $user = User::find($news_count['user_id']);
            if ($user) {
                $list[] = [
                    'user' => $user,
                    'text' => 'добавил(а) ',
                    'value' => $news_count['sum_count'],
                    'after_text'=> $this->pluralize($news_count['sum_count'], ['статью', 'статьи', 'статей']),
                ];
            }
        }
        return view('pages.top-list.index', [
            'links' => $this->links,
            'list' => $list
        ]);
    }

    public function forum() {
        $forum_messages = ForumMessage::select(DB::raw('user_id, count(user_id) as sum_count'))->groupBy('user_id')->orderBy('sum_count', 'desc')->limit(100)->get();
        $list = [];
        foreach ($forum_messages as $forum_messages_count) {
            $user = User::find($forum_messages_count['user_id']);
            if ($user) {
                $list[] = [
                    'user' => $user,
                    'text' => 'оставил(а) ',
                    'value' => $forum_messages_count['sum_count'],
                    'after_text'=> $this->pluralize($forum_messages_count['sum_count'], ['сообщение', 'сообщения', 'сообщений']),
                ];
            }
        }
        return view('pages.top-list.index', [
            'links' => $this->links,
            'list' => $list
        ]);
    }


    public function comments() {
        $comments = Comment::select(DB::raw('user_id, count(user_id) as sum_count'))->groupBy('user_id')->orderBy('sum_count', 'desc')->limit(100)->get();
        $list = [];
        foreach ($comments as $comments_count) {
            $user = User::find($comments_count['user_id']);
            if ($user) {
                $list[] = [
                    'user' => $user,
                    'text' => 'оставил(а) ',
                    'value' => $comments_count['sum_count'],
                    'after_text'=> $this->pluralize($comments_count['sum_count'], ['комментарий', 'комментария', 'комментариев']),
                ];
            }
        }
        return view('pages.top-list.index', [
            'links' => $this->links,
            'list' => $list
        ]);
    }

    public function awards() {
        $awards = Award::all();
        $users_cache = [];
        $list = [];
        foreach ($awards as $award) {
            $grouped = $award->userAwards->groupBy('to_id')->sortByDesc(function ($user_awards) {
                return count($user_awards);
            });
            $data = [
                'award' => $award,
                'users' => []
            ];
            foreach ($grouped as $user_id => $user_awards) {
                if (isset($users_cache[$user_id])) {
                    $user = $users_cache[$user_id];
                } else {
                    $user = User::find($user_id);
                    $users_cache[$user_id] = $user;
                }
                if ($user) {
                    $data['users'][] = [
                        'id' => $user->id,
                        'username' => $user->username,
                        'url' => $user->url,
                        'count' => count($user_awards)
                    ];
                }
            }
            if (count($data['users']) > 0) {
                $list[] = $data;
            }
        }
        return view('pages.top-list.awards', [
            'links' => $this->links,
            'list' => $list
        ]);
    }

}
