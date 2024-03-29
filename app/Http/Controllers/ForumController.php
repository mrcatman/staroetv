<?php

namespace App\Http\Controllers;

use App\Forum;
use App\ForumMessage;
use App\ForumTopic;
use App\ForumTracking;
use App\Helpers\BBCodesHelper;
use App\Helpers\DatesHelper;
use App\Helpers\ForumPaginator;
use App\Helpers\PermissionsHelper;
use App\Helpers\ViewsHelper;
use App\Notifications\NewForumReply;
use App\QuestionnaireAnswer;
use App\User;
use Carbon\Carbon;
use http\Message;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class ForumController extends Controller {

    protected $messages_on_page = 20;
    protected $topics_on_page = 25;
    protected $cache_time = 3600;
    protected $online_time = 10;

    public function index()
    {
        $search = request()->input('s', '');
        if ($search != "") {
            $page = request()->input('page', 1);
            $messages_view = (request()->input('type', '') == "messages") || request()->input('messages_view', false);
            $subforum_ids = $this->filterSubforums(Forum::pluck('id'));
            if (!$messages_view) {
                $topics = ForumTopic::where(function ($q) use ($search) {
                    $q->where('title', 'LIKE', '%' . $search . '%');
                    $q->orWhere('description', 'LIKE', '%' . $search . '%');
                })->whereIn('forum_id', $subforum_ids);
                $total = $topics->count();
                $topics_on_page = $this->topics_on_page;

                $topics = $topics->limit($topics_on_page)->offset(($page - 1) * $topics_on_page)->orderBy('last_reply_at', 'DESC');
                $topics = $topics->get();

                $paginator = new ForumPaginator([], $total, $topics_on_page, $page, [
                    'path' => ForumPaginator::resolveCurrentPath(),
                ]);
                $fixed_topics = [];
                return view("pages.forum.subforum", [
                    'messages_view' => false,
                    'search' => $search,
                    'paginator' => $paginator,
                    'fixed_topics' => $fixed_topics,
                    'topics' => $topics,
                    'parent_forum' => null,
                    'forum' => null,
                ]);
            } else {
                Paginator::currentPageResolver(function () use ($page) {
                    return $page;
                });

                $subforum_ids = $this->filterSubforums(Forum::pluck('id'));
                $topic_ids = ForumTopic::whereIn('forum_id', $subforum_ids)->pluck('id');
                $messages = ForumMessage::whereRaw("MATCH (content) AGAINST ('$search')");
                $messages = $messages->whereIn('topic_id', $topic_ids);
                $total = $messages->count();
                $messages = $messages->orderBy('id', 'asc')->limit($this->messages_on_page)->offset(($page - 1) * $this->messages_on_page)->get();

                //$messages = $messages->paginate($this->messages_on_page);
                $paginator = new ForumPaginator([], $total, $this->messages_on_page, $page, [
                    'path' => ForumPaginator::resolveCurrentPath(),
                ]);
                return view("pages.forum.subforum", [
                    'messages_view' => true,
                    'messages' => $messages,
                    'search' => $search,
                    'paginator' => $paginator,
                    'fixed_topics' => [],
                    'topics' => [],
                    'parent_forum' => null,
                    'forum' => null,
                ]);
            }
        } else {
            $forums = Forum::where(['parent_id' => 0])->get();

            $users_on_forum = User::where('last_page_seen', 'LIKE', '%forum%')->where('was_online', '>=', Carbon::now()->subMinutes($this->online_time))->get();
            $users_by_subforum = [];
            foreach ($users_on_forum as $user) {
                $last_page = $user->last_page_seen;
                $last_page = explode("/", $last_page);
                if (count($last_page) > 1) {
                    $subforum = explode("-", $last_page[1])[0];
                    if (!isset($users_by_subforum[$subforum])) {
                        $users_by_subforum[$subforum] = [];
                    };
                    $users_by_subforum[$subforum][] = $user;
                }
            }
            $forums->transform(function ($forum) use ($users_by_subforum) {
                $forum->subforums = $forum->subforums->map(function ($subforum) use ($users_by_subforum) {
                    if (isset($users_by_subforum[$subforum->id])) {
                        $subforum->users = $users_by_subforum[$subforum->id];
                    }
                    return $subforum;
                })->filter(function ($subforum) {
                    return PermissionsHelper::checkGroupAccess('can_view', $subforum);
                });
                return $forum;
            });
            $stats = [
                'messages_count' => ForumMessage::count(),
                'topics_count' => ForumTopic::count(),
                'users_count' => User::count(),
                'last_user' => User::orderBy('id', 'desc')->first()
            ];

            return view("pages.forum.index", [
                'search' => $search,
                'stats' => $stats,
                'users_on_forum' => $users_on_forum,
                'forums' => $forums,
            ]);
        }
    }

    public function subforum($id, $page = 1) {
        $forum = Forum::find($id);
        $parent_forum = Forum::find($forum->parent_id);
        if (!PermissionsHelper::checkGroupAccess('can_view', $forum)) {
            return redirect('https://staroetv.su/forum');
        }

        $search = request()->input('s', '');
        $messages_view = ($search != "" && request()->input('type', '') == "messages") || request()->input('messages_view', false);
        if (!$messages_view) {
            $topics_on_page = $this->topics_on_page;
            $subforum_ids = Forum::where(['parent_id' => $forum->id])->pluck('id');
            $subforum_ids->push($forum->id);
            $subforum_ids = $this->filterSubforums($subforum_ids);
            $topics = ForumTopic::whereIn('forum_id', $subforum_ids);
            if ($search != "") {
                $topics = $topics->where(function ($q) use ($search) {
                    $q->where('title', 'LIKE', '%' . $search . '%');
                    $q->orWhere('description', 'LIKE', '%' . $search . '%');
                });
                $fixed_topics = [];
            } else {
                $topics = $topics->where(['is_fixed' => false]);
                $fixed_topics = $forum->fixed_topics;
            }
            $total = $topics->count();
            $topics = $topics->limit($topics_on_page)->offset(($page - 1) * $topics_on_page)->orderBy('last_reply_at', 'DESC');
            $topics = $topics->get();

            $users_on_forum = User::where('last_page_seen', 'LIKE', '%forum/' . $forum->id . '%')->whereDate('was_online', '>=', Carbon::now()->subMinutes($this->online_time))->get();
            $users_by_topic = [];

            foreach ($users_on_forum as $user) {
                $last_page = $user->last_page_seen;
                $last_page = explode("/", $last_page);
                $topic = explode("-", $last_page[1]);
                if (isset($topic[1])) {
                    $topic = $topic[1];
                    if (!isset($users_by_subforum[$topic])) {
                        $users_by_topic[$topic] = [];
                    };
                    $users_by_topic[$topic][] = $user;
                }
            }


            foreach ($topics as $topic) {
                if (isset($users_by_topic[$topic->id])) {
                    $topic->users = $users_by_topic[$topic->id];
                }
            };

            foreach ($fixed_topics as $topic) {
                if (isset($users_by_topic[$topic->id])) {
                    $topic->users = $users_by_topic[$topic->id];
                }
            };

            $paginator = new ForumPaginator([], $total, $topics_on_page, $page, [
                'path' => ForumPaginator::resolveCurrentPath(),
                'forum_id' => $forum->id,
            ]);

            return view("pages.forum.subforum", [
                'messages_view' => false,
                'search' => $search,
                'paginator' => $paginator,
                'fixed_topics' => $fixed_topics,
                'topics' => $topics,
                'parent_forum' => $parent_forum,
                'forum' => $forum,
            ]);
        } else {
            Paginator::currentPageResolver(function () use ($page) {
                return $page;
            });

            $messages_on_page = $this->messages_on_page;
            $subforum_ids = Forum::where(['parent_id' => $forum->id])->pluck('id');
            $subforum_ids->push($forum->id);
            $subforum_ids = $this->filterSubforums($subforum_ids);
            $topic_ids = ForumTopic::whereIn('forum_id', $subforum_ids)->pluck('id');
            $messages = ForumMessage::whereRaw("MATCH (content) AGAINST ('$search')");
            $messages = $messages->whereIn('topic_id', $topic_ids);
            $total = $messages->count();
            $messages = $messages->paginate($messages_on_page);
            $paginator = new ForumPaginator([], $total, $messages_on_page, $page, [
                'path' => ForumPaginator::resolveCurrentPath(),
                'forum_id' => $forum->id,
            ]);
            return view("pages.forum.subforum", [
                'messages_view' => true,
                'messages' => $messages,
                'search' => $search,
                'paginator' => $paginator,
                'fixed_topics' => [],
                'topics' => [],
                'parent_forum' => $parent_forum,
                'forum' => $forum,
            ]);
        }
    }

    private function filterSubforums($forum_ids) {
        $forums = Forum::whereIn('id', $forum_ids)->get();
        $forums->filter(function($forum) {
            return PermissionsHelper::checkGroupAccess('can_view', $forum);
        });
        return $forums->pluck('id');
    }

    private function updateTracking($topic) {
        if ($user = auth()->user()) {
            $topic_tracking = ForumTracking::firstOrNew(['user_id' => $user->id, 'is_forum' => false, 'entity_id' => $topic->id]);
            $topic_tracking->timestamp = time();
            $topic_tracking->save();
        }
    }

    public function showTopic($forum_id, $topic_id, $page = 1) {
        $page = (int)$page;
        $forum = null;
        $subforum = Forum::find($forum_id);
        if ($subforum) {
            $forum = Forum::find($subforum->parent_id);
            if (!PermissionsHelper::checkGroupAccess('can_view', $subforum)) {
                return redirect('https://staroetv.su/forum');
            }
        }

        $cache_time = $this->cache_time;
        $messages_on_page = $this->messages_on_page;

        $topic = ForumTopic::where(['id' => $topic_id])->first();
        if (!$topic) {
            return redirect("https://staroetv.su/forum");
        }
        if ($topic->forum_id != $forum_id) {
            return redirect("https://staroetv.su/forum/$topic->forum_id-$topic_id-1");
        }
        $this->updateTracking($topic);
        ViewsHelper::increment($topic, 'forum_topics', 'views_count');
        $topic->save();

        $search = request()->input('s');

        $fixed_message = null;
        if ($topic->first_message_fixed && !$search) {
            $fixed_message = ForumMessage::where(['topic_id' => $topic_id])->orderBy('id', 'asc')->first();
        }

        $messages = null;

        $cache_tag = 'msg_' . $topic_id . '_' . $page;
        $total_tag = 'total_' . $topic_id . '_' . $page;
        if (!$search) {
            $messages = Cache::get($cache_tag);
        }

        if (!$messages) {
            $messages = ForumMessage::where(['topic_id' => $topic_id]);
            if ($fixed_message) {
                $messages = $messages->where('id', '!=', $fixed_message->id);
            }
            $messages = $messages->with('user')->orderBy('id', 'asc');
            if ($search) {
                $messages = $messages->where('content', 'like', '%'.$search.'%');
            }
            $total = $messages->count();
            $messages = $messages->limit($messages_on_page)->offset(($page - 1) * $messages_on_page)->get();
            if (!$search) {
                Cache::put($cache_tag, $messages, $cache_time);
                Cache::put($total_tag, $total, $cache_time);
            }
        } else {
            $total = Cache::get($total_tag);
        }
        if ($page > floor($total / $messages_on_page)) {
            Cache::forget($total_tag);
            Cache::forget($cache_tag);
        }

        $paginator = new ForumPaginator([], $total, $messages_on_page, $page, [
            'path'  => ForumPaginator::resolveCurrentPath(),
            'forum_id' => $subforum->id,
            'topic_id' => $topic->id,
        ]);


        $show_pager = $total > $messages_on_page;
        $users = $messages->pluck('user')->unique()->filter();
        $show_results = null;
        if ($topic->questionnaire_data) {
            $show_results = !PermissionsHelper::allows('frdopoll') || QuestionnaireAnswer::where(['questionnaire_id' => $topic->questionnaire_data->id, 'user_id' => auth()->user()->id])->count() > 0;
        }

        return view("pages.forum.topic", [
            'show_results' => $show_results,
            'search' => $search,
            'show_pager' => $show_pager,
            'topic' => $topic,
            'messages' => $messages,
            'fixed_message' => $fixed_message,
            'paginator' => $paginator,
            'users' => $users,
            'forum' => $forum,
            'subforum' => $subforum
        ]);
    }

    public function redirectToMessage($forum_id, $topic_id, $message_id) {
        $message_index = ForumMessage::where(['topic_id' => $topic_id])->pluck('id')->search($message_id);
        if ($message_index === false) {
            return redirect("https://staroetv.su/forum");
        }
        $topic = ForumTopic::find($topic_id);
        if ($topic->first_message_fixed) {
            $message_index--;
        }
        $page = ceil( $message_index / $this->messages_on_page);
        return redirect("https://staroetv.su/forum/$forum_id-$topic_id-$page#$message_id");
    }

    public function redirectToMessageById($message_id) {
        $message = ForumMessage::find($message_id);
        if (!$message) {
            return redirect("https://staroetv.su/forum");
        }
        $topic = ForumTopic::find($message->topic_id);
        if (!$topic) {
            return redirect("https://staroetv.su/forum");
        }
        $message_index = ForumMessage::where(['topic_id' => $message->topic_id])->pluck('id')->search($message_id);
        if ($message_index === false) {
            return redirect("https://staroetv.su/forum");
        }
        if ($topic->first_message_fixed) {
            $message_index--;
        }
        $page = ceil( $message_index / $this->messages_on_page);

        return redirect("https://staroetv.su/forum/".$topic->forum_id."-".$topic->id."-".$page."#".$message_id);
    }

    public function redirectToLastMessage($forum_id, $topic_id) {
        $messages = ForumMessage::where(['topic_id' => $topic_id])->pluck('id');
        if (count($messages) === 0) {
            return redirect("https://staroetv.su/forum");
        }
        $topic = ForumTopic::find($topic_id);
        $message_id = $messages->last();
        $messages_count = count($messages);
        if ($topic->first_message_fixed) {
            $messages_count--;
        }
        $page = ceil( $messages_count / $this->messages_on_page);
        return redirect("https://staroetv.su/forum/$forum_id-$topic_id-$page#$message_id");
    }

    public function getEditForm() {
        $message_id = request()->input('message_id');
        $message = ForumMessage::find($message_id);
        if (!$message) {
            return ['status' => 0, 'text' => 'Сообщение не найдено'];
        }
        $original_bb = BBCodesHelper::HTMLToBB($message->original_text);
        return [
            'status' => 1,
            'data' => [
                'html' =>  view("blocks/forum_form", ['inline' => true, 'edit_id' => $message->id, 'topic_id' => $message->topic_id, 'content' => $original_bb])->render()
            ]
        ];
    }

    private function deleteCache($topic_id, $page) {
        $cache_tag = 'msg_' . $topic_id . '_' . $page;
        Cache::forget($cache_tag);
        $total_tag = 'total_' . $topic_id . '_' . $page;
        Cache::forget($total_tag);
        $topic = ForumTopic::find($topic_id);
        $topic->answers_count = ForumMessage::where(['topic_id' => $topic->id])->count() - 1;
        $topic->save();
    }

    private function getPageIndex($message) {
        $message_index = ForumMessage::where(['topic_id' => $message->topic_id])->pluck('id')->search($message->id);
        $page = ceil( $message_index / $this->messages_on_page);
        return $page;
    }

    public function editMessage() {
        $message_id = request()->input('message_id');
        $message = ForumMessage::find($message_id);
        if (!$message) {
            return ['status' => 0, 'text' => 'Сообщение не найдено'];
        }
        if (!$message->can_edit) {
            return ['status' => 0, 'text' => 'Вы не можете редактировать данное сообщение'];
        }
        if (PermissionsHelper::isBanned()) {
            return ['status' => 0, 'text' => 'Вы забанены'];
        }
        $message->edited_at = Carbon::now();
        $message->edited_by = auth()->user()->username;
        $message->content = BBCodesHelper::BBToHTML(request()->input('message'));
        $message->save();

        $page = $this->getPageIndex($message);
        $this->deleteCache($message->topic_id, $page);
        $selector = '.forum-message[data-id="'.$message->id.'"]';
        return [
            'status' => 1,
            'text' => 'Пост сохранен',
            'data' => [
                'dom' => [
                    [
                        'replace' => $selector,
                        'html' => view("blocks/forum_message", ['inner' => true, 'ajax' => true, 'fixed' => $message->is_fixed, 'message' => $message])->render()
                    ]
                ]
            ]
        ];
    }

    private function recalculateLastMessage($topic, $forum) {
        if ($topic) {
            $last_message = ForumMessage::where(['topic_id' => $topic->id])->orderBy('id', 'desc')->first();
            $topic->topic_last_username = $last_message->username;
            $topic->last_reply_at = Carbon::createFromTimestamp($last_message->created_at_ts);
            $topic->save();
        }
        $topic_ids = ForumTopic::where(['forum_id' => $forum->id])->pluck('id');
        $last_message = ForumMessage::whereIn('topic_id', $topic_ids)->orderBy('id', 'desc')->first();
        $forum->last_username = $last_message->username;
        $forum->last_topic_id = $last_message->topic->id;
        $forum->last_topic_name = $last_message->topic->title;
        $forum->last_reply_at =  Carbon::createFromTimestamp($last_message->created_at_ts);
        $forum->save();
    }

    public function deleteMessage() {
        $message_id = request()->input('message_id');
        $message = ForumMessage::find($message_id);
        if (!$message) {
            return ['status' => 0, 'text' => 'Сообщение не найдено'];
        }
        if (PermissionsHelper::isBanned()) {
            return ['status' => 0, 'text' => 'Вы забанены'];
        }
        if (!$message->can_edit) {
            return ['status' => 0, 'text' => 'Вы не можете удалить данное сообщение'];
        }
        $messages = ForumMessage::where(['topic_id' => $message->topic_id])->pluck('id');
        $last_message_id = $messages->last();
        $page = $this->getPageIndex($message);
        $message->delete();
        $this->deleteCache($message->topic_id, $page);
        if ($message_id == $last_message_id) {
            $this->recalculateLastMessage($message->topic, $message->topic->forum);
        }
        return [
            'status' => 1,
            'text' => 'Сообщение удалено',
            'data' => [
                'dom' => [
                    [
                        'replace' => "#".$message->id,
                        'html' => ""
                    ]
                ]
            ]
        ];
    }

    public function postMessage($topic_id = null) {
        $user = auth()->user();
        if (!$user) {
            return ['status' => 0, 'text' => 'Вы не авторизованы'];
        }
        if (PermissionsHelper::isBanned()) {
            return ['status' => 0, 'text' => 'Вы забанены'];
        }
        $topic = ForumTopic::find($topic_id ? $topic_id : request()->input('topic_id'));
        if (!$topic) {
            return ['status' => 0, 'text' => 'Тема не найдена'];
        }
        if ($topic->is_closed) {
            return ['status' => 0, 'text' => 'Тема закрыта'];
        }
        $subforum = Forum::find($topic->forum_id);
        if (!PermissionsHelper::allows('frcloset') && (!PermissionsHelper::checkGroupAccess('can_post', $subforum) || !PermissionsHelper::allows('frreply'))) {
            return ['status' => 0, 'text' => 'У вас нет прав для отправки сообщений в эту тему'];
        }
        $content = request()->input('message');
        if ($content == "") {
            return ['status' => 0, 'text' => 'Введите сообщение'];
        }
        $quote_users = [];
        $quotes = [];
        $users_not_to_reply = [];
        foreach (explode(PHP_EOL, $content) as $line) {
            preg_match('/\[quote(.*?)](.*?)\[\/quote(.*?)\]/', $line, $quoteData);
            if (count($quoteData) >= 3) {
                $quoteInfo = explode("=", $quoteData[1]);
                if (count($quoteInfo) > 1) {

                    $quoteParams = explode(";", $quoteInfo[1]);

                    if (count($quoteParams) === 1) {
                        $quoteName = $quoteParams[0];
                        $userData = User::where(['username' => $quoteName])->first();
                        if ($userData && $userData->id !== $user->id && !in_array($userData->id, $users_not_to_reply)) {
                            $quote_users[] = $userData;
                            $users_not_to_reply[] = $userData->id;
                        }
                    }
                    $message = ForumMessage::find($quoteParams[1]);
                    if ($message && $message->user) {
                        $replyTo = $quoteData[2];
                        $quotes[] = [
                            'reply_to' => $replyTo,
                            'user' => $message->user
                        ];
                    }
                }
            }
        }
        $contentWithoutQuotes = preg_replace('/\[quote(.*?)](.*?)\[\/quote(.*?)\]/', '', $content);
        $content = BBCodesHelper::BBToHTML($content);
        $contentByLines = explode("<br>", $content);
        $contentWithoutQuotes = explode(PHP_EOL, $contentWithoutQuotes);
        $contentWithoutQuotes = array_filter($contentWithoutQuotes, function($string) {
            return $string != "" && $string != "\r";
        });
        $contentWithoutQuotes = array_values($contentWithoutQuotes);
        if (count($contentWithoutQuotes) > 0) {
            $contentWithoutQuotes = $contentWithoutQuotes[0];
        } else {
            $contentWithoutQuotes = "";
        }

        foreach ($quotes as &$quote) {
            $userData = $quote['user'];
            $contentData = $contentWithoutQuotes;
            foreach ($contentByLines as $index => $line) {
                if (mb_strpos($line, $userData->username, 0, "UTF-8") !== false) {
                    $stringIndex = $index + 1;
                    $quoteResponse = null;
                    while (isset($contentByLines[$stringIndex]) && !$quoteResponse) {
                        if (mb_strpos($contentByLines[$stringIndex], "uzquote", 0, "UTF-8") === false) {
                            $quoteResponse = $contentByLines[$stringIndex];
                        }
                        $stringIndex++;
                    }
                    if ($quoteResponse) {
                        $contentData = $quoteResponse;
                    }
                }
            }
            $quote['text'] = $contentData;
        }
        $last_message = null;
        if (PermissionsHelper::allows('frmerge')) {
            $last_message = ForumMessage::where(['topic_id' => $topic->id])->orderBy('id', 'DESC')->first();
        }
        if ($last_message && $last_message->user_id == $user->id) {
            $last_message->content = $last_message->content."<br><br><b>Добавлено</b> (".DatesHelper::formatTS(time()).")<br>---------------------------------------------<br>".$content;
            $last_message->save();
            $messages = ForumMessage::where(['topic_id' => $topic->id])->get();
            $page = ceil(count($messages) / $this->messages_on_page);
            $this->deleteCache($topic->id, $page);
            $this->updateTracking($topic);

            return [
                'status' => 1,
                'text' => 'Сообщение добавлено',
                'data' => [
                    'message' => $last_message,
                    'last_page' => $page,
                    '_dom' => [
                        [
                            'replace' => "#".$last_message->id,
                            'html' => view("blocks/forum_message",  ['inner' => true, 'fixed' => false, 'message' => $last_message])->render()
                        ]
                    ]
                ]
            ];
        }
        $message_obj = new ForumMessage([
            'topic_id' => $topic->id,
            'is_first' => false,
            'content' => $content,
            'username' => $user->username,
            'edited_by' => '',
            'ip' => request()->header('x-real-ip', request()->ip()),
            'questionnaire' => '',
            'user_id' => $user->id,
        ]);
        $message_obj->save();

        $topic->topic_last_username = $user->username;
        $topic->last_reply_at = Carbon::now();
        $topic->save();

        $subforum->last_username = $user->username;
        $subforum->last_topic_id = $topic->id;
        $subforum->last_topic_name = $topic->title;
        $subforum->last_reply_at = Carbon::now();
        $subforum->save();

       // $forum = Forum::find($subforum->parent_id);

       // $forum->last_username = $user->username;
       // $forum->last_topic_id = $topic->id;
       // $forum->last_topic_name = $topic->title;
       // $forum->last_reply_at = Carbon::now();
       // $forum->save();

        $messages = ForumMessage::where(['topic_id' => $topic->id])->get();
        $messages_count = count($messages);
        if ($topic->first_message_fixed) {
            $messages_count--;
        }
        $page = ceil($messages_count / $this->messages_on_page);
        $this->deleteCache($topic->id, $page);

        foreach ($quotes as $quote) {
            $quote['user']->notify(new NewForumReply($message_obj, $quote));
        }
        return [
            'status' => 1,
            'text' => 'Сообщение добавлено',
            'data' => [
                'message' => $message_obj,
                'last_page' => $page,
                '_dom' => [
                    [
                        'append_to' => ".forum-section__messages",
                        'html' => view("blocks/forum_message",  ['fixed' => false, 'message' => $message_obj])->render()
                    ]
                ]
            ]
        ];
    }

    public function newTopic($id) {
        $forum = Forum::find($id);
        $parent_forum = Forum::find($forum->parent_id);
        return view("pages.forum.topic_form", [
            'questionnaire' => null,
            'forum_id' => $id,
            'topic' => null,
            'parent_forum' => $parent_forum,
            'forum' => $forum,
        ]);
    }

    public function editTopic($id) {
        $topic = ForumTopic::find($id);
        $forum = Forum::find($topic->forum_id);
        $parent_forum = Forum::find($forum->parent_id);
        $questionnaire = $topic->questionnaire_data;
        if ($questionnaire) {
            $questionnaire->load('variants');
            $questionnaire = $questionnaire->toArray();
        }
        return view("pages.forum.topic_form", [
            'questionnaire' => $questionnaire,
            'forum_id' => $id,
            'topic' => $topic,
            'parent_forum' => $parent_forum,
            'forum' => $forum,
        ]);
    }

    private function getTopicValidateRules($create = false) {
        $rules = [
            'title' => 'required|min:1',
            'description' => 'sometimes',
        ];
        if ($create) {
            $rules['message'] = 'required|min:1';
        }
        if (PermissionsHelper::allows('frmesont')) {
            $rules['first_message_fixed'] = 'sometimes|boolean';
        }
        if (PermissionsHelper::allows('frmesont')) {
            $rules['is_fixed'] = 'sometimes|boolean';
        }
        if (PermissionsHelper::allows('frmesont')) {
            $rules['is_closed'] = 'sometimes|boolean';
        }
        return $rules;
    }

    public function createTopic($id) {
        $forum = Forum::find($id);
        if (!$forum) {
            return [
                'status' => 0,
                'text' => 'Форум не найден'
            ];
        }
        if (!$forum->can_create_new_topic) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }
        $data = request()->validate($this->getTopicValidateRules(true));
        $content = $data['message'];
        unset($data['message']);

        $user = auth()->user();

        $topic = new ForumTopic($data);
        $topic->topic_starter_id = $user->id;
        $topic->topic_starter_username = $user->username;
        $topic->topic_last_username = $user->username;
        $topic->last_reply_at = Carbon::now();

        $topic->forum_id = $forum->id;
        $topic->save();


        $message_obj = new ForumMessage([
            'topic_id' => $topic->id,
            'is_first' => true,
            'content' => BBCodesHelper::BBToHTML($content),
            'username' => $user->username,
            'edited_by' => '',
            'ip' => request()->header('x-real-ip', request()->ip()),
            'questionnaire' => '',
            'user_id' => $user->id,
        ]);
        $message_obj->save();
        if (request()->input('questionnaire') && PermissionsHelper::allows('frpoll')) {
            try {
                (new QuestionnairesController())->save($topic->id);
            } catch (\Exception $e) {
                return ['status' => 0, 'text' => $e->getMessage()];
            }
        }
        $this->recalculateLastMessage(null, $topic->forum);
        $this->updateTracking($topic);
        return [
            'status' => 1,
            'text' => 'Тема создана',
            'redirect_to' => '/forum/'.$forum->id.'-'.$topic->id.'-1'
        ];
    }

    public function saveTopic($id) {
        $topic = ForumTopic::find($id);
        if (!$topic) {
            return [
                'status' => 0,
                'text' => 'Тема не найдена'
            ];
        }
        if (!$topic->can_edit) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }

        $data = request()->validate($this->getTopicValidateRules(false));
        if (request()->has('questionnaire_data') && PermissionsHelper::allows('frpoll')) {

            try {
                (new QuestionnairesController())->save($id);
            } catch (\Exception $e) {
                return ['status' => 0, 'text' => $e->getMessage()];
            }
        }
        $topic->fill($data);
        $topic->save();
        $this->deleteCache($topic->id, 1);
        return [
            'status' => 1,
            'text' => 'Тема сохранена',
            'redirect_to' => '/forum/'.$topic->forum_id.'-'.$topic->id.'-1'
        ];
    }

    public function deleteTopic() {
        $topic = ForumTopic::find(request()->input('topic_id'));
        if (!$topic) {
            return [
                'status' => 0,
                'text' => 'Тема не найдена'
            ];
        }
        if (!$topic->can_delete) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }
        $topic->delete();
        ForumMessage::where(['topic_id' => $topic->id])->delete();
        $this->recalculateLastMessage(null, $topic->forum);
        return [
            'status' => 1,
            'text' => 'Тема удалена',
            'redirect_to' => '/forum/'.$topic->forum_id
        ];
    }

    public function moveTopic() {
        $topic = ForumTopic::find(request()->input('topic_id'));
        if (!$topic) {
            return [
                'status' => 0,
                'text' => 'Тема не найдена'
            ];
        }
        if (!PermissionsHelper::allows('frreplthr')) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }
        $forum = Forum::find(request()->input('forum_id'));
        if (!$forum) {
            return [
                'status' => 0,
                'text' => 'Форум не найден'
            ];
        }
        if ($forum->parent_id < 1) {
            return [
                'status' => 0,
                'text' => 'Невозможно переместить тему в данный форум'
            ];
        }
        $topic->forum_id = $forum->id;
        $topic->save();
        return [
            'status' => 1,
            'text' => 'Тема перемещена',
            'redirect_to' => '/forum/'.$topic->forum_id.'-'.$topic->id.'-1'
        ];
    }


    public function newForum($parent_id) {
        $forum = null;
        $parent = Forum::find($parent_id);
        $sections = Forum::where('parent_id', '<', '1')->get();
        $forums = Forum::where('parent_id', '>', '0')->get();
        return view("pages.forum.form", [
            'parent' => $parent,
            'forums' => $forums,
            'is_section' => $parent_id == 0,
            'parent_id' => $parent_id,
            'forum' => null,
            'sections' => $sections
        ]);
    }

    public function editForum($id) {
        $forum = Forum::find($id);
        $parent = Forum::find($forum->parent_id);
        $sections = Forum::where('parent_id', '<', '1')->where('id','!=', $id)->get();
        $forums = Forum::where('parent_id', '>', '0')->get();
        return view("pages.forum.form", [
            'forums' => $forums,
            'is_section' => $forum->parent_id < 1,
            'parent' => $parent,
            'parent_id' => $forum->parent_id,
            'forum' => $forum,
            'sections' => $sections
        ]);
    }

    public function saveForum($id) {
        if (!PermissionsHelper::allows('fredit')) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }
        $forum = Forum::find($id);
        if (!$forum) {
            return [
                'status' => 0,
                'text' => 'Форум не найден'
            ];
        }
        $data = request()->validate([
            'can_create_topics' => 'sometimes',
            'can_post' => 'sometimes',
            'can_view' => 'sometimes',
            'description' => 'sometimes',
            'parent_id' => 'sometimes',
            'state' => 'sometimes',
            'title' => 'required|min:1',
            'move_to' => 'sometimes',
            'move_subforums_to' => 'sometimes'
        ]);
        $fill_data = $data;
        unset($fill_data['move_to']);
        unset($fill_data['move_subforums_to']);
        $forum->fill($fill_data);
        if (isset($data['move_subforums_to']) && $data['move_subforums_to'] > 0) {
            $move_to = Forum::find($data['move_subforums_to']);
            if (!$move_to) {
                return [
                    'status' => 0,
                    'text' => 'Форум для переноса не найден'
                ];
            }
            Forum::where(['parent_id' => $forum->id])->update(['parent_id' => $move_to->id]);
            $forum->delete();
            return [
                'status' => 1,
                'text' => 'Форум удален',
                'redirect_to' => '/forum/'
            ];
        }
        if (isset($data['state']) && $data['state'] == "4") {
            $move_to = Forum::find($data['move_to']);
            if (!$move_to) {
                return [
                    'status' => 0,
                    'text' => 'Форум для переноса не найден'
                ];
            }
            ForumTopic::where(['forum_id' => $forum->id])->update(['forum_id' => $move_to->id]);
            $forum->delete();
            return [
                'status' => 1,
                'text' => 'Форум удален',
                'redirect_to' => '/forum/'
            ];
        } else {
            $forum->save();
            return [
                'status' => 1,
                'text' => 'Форум сохранен',
                'redirect_to' => '/forum/'.$forum->id
            ];
        }

    }

    public function createForum() {
        if (!PermissionsHelper::allows('fredit')) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }
        $data = request()->validate([
            'can_create_topics' => 'sometimes',
            'can_post' => 'sometimes',
            'can_view' => 'sometimes',
            'description' => 'sometimes',
            'parent_id' => 'sometimes',
            'state' => 'sometimes',
            'title' => 'required|min:1',
        ]);
        if (!isset($data['parent_id'])) {
            $data['parent_id'] = "0";
        }
        $forum = new Forum($data);
        $forum->topics_count = 0;
        $forum->replies_count = 0;
        $forum->save();
        return [
            'status' => 1,
            'text' => 'Форум сохранен',
            'redirect_to' => '/forum/'.$forum->id
        ];
    }

    public function getProfile($message_id) {
        $message = ForumMessage::find($message_id);
        if (!$message) {
            return [
                'status' => 0,
                'text' => 'Сообщение не существует'
            ];
        }
        if (!$message->user) {
            return [
                'status' => 0,
                'text' => 'Пользователь не найден'
            ];
        }
        return [
            'status' => 1,
            'data' => [
                'title' => 'Профиль пользователя '.$message->user->username,
                'html' => view("blocks/forum_profile", [ 'message' => $message])->render()
            ]
        ];
    }

    public function lastTopics() {
        $forums = Forum::all()->filter(function ($forum) {
            return PermissionsHelper::checkGroupAccess('can_view', $forum);
        });
        $topics = ForumTopic::whereIn('forum_id', $forums->pluck('id'));
        $topics_on_page = $this->topics_on_page;
        $topics = $topics->orderBy('last_reply_at', 'DESC')->paginate($topics_on_page);

        return view("pages.forum.subforum", [
            'title' => 'Ленточный вариант форума',
            'messages_view' => false,
            'search' => null,
            'paginator' => $topics,
            'fixed_topics' => [],
            'topics' => $topics,
            'parent_forum' => null,
            'forum' => null,
        ]);
    }

    public function userMessages($user_id) {
        $user = User::find($user_id);
        if (!$user) {
            return redirect('https://staroetv.su/forum');
        }
        $forums = Forum::all()->filter(function ($forum) {
            return PermissionsHelper::checkGroupAccess('can_view', $forum);
        });
        $topic_ids = ForumTopic::whereIn('forum_id', $forums->pluck('id'))->pluck('id');

        $messages_on_page = $this->messages_on_page;
        $messages = ForumMessage::whereIn('topic_id', $topic_ids)->where(['user_id' => $user->id])->orderBy('id', 'asc')->paginate($messages_on_page);

        return view("pages.forum.subforum", [
            'title' => '<div>Записи участника <strong> '.strip_tags($user->username).'</strong></div>',
            'messages_view' => true,
            'messages' => $messages,
            'search' => null,
            'paginator' => $messages,
            'fixed_topics' => [],
            'topics' => [],
            'parent_forum' => null,
            'forum' => null,
        ]);
    }
}
