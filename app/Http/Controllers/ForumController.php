<?php

namespace App\Http\Controllers;

use App\Forum;
use App\ForumMessage;
use App\ForumTopic;
use App\Helpers\BBCodesHelper;
use App\Helpers\DatesHelper;
use App\Helpers\ForumPaginator;
use App\Helpers\PermissionsHelper;
use Carbon\Carbon;
use http\Message;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class ForumController extends Controller {

    protected $messages_on_page = 20;
    protected $cache_time = 3600;

    public function index() {
        $forums = Forum::where(['parent_id' => 0])->get();
        $forums->transform(function($forum) {
             $forum->subforums = $forum->subforums->filter(function($subforum) {
                 return PermissionsHelper::checkSubforumAccess('can_view', $subforum);
             });
             return $forum;
        });
        return view("pages.forum.index", [
            'forums' => $forums,
        ]);
    }

    public function subforum($id) {
        $forum = Forum::find($id);
        $parent_forum = Forum::find($forum->parent_id);
        if (!PermissionsHelper::checkSubforumAccess('can_view', $forum)) {
            return redirect('/forum');
        }
        return view("pages.forum.subforum", [
            'parent_forum' => $parent_forum,
            'forum' => $forum,
        ]);
    }

    public function showTopic($forum_id, $topic_id, $page = 1) {
        $forum = null;
        $subforum = Forum::find($forum_id);
        if ($subforum) {
            $forum = Forum::find($subforum->parent_id);
            if (!PermissionsHelper::checkSubforumAccess('can_view', $subforum)) {
                return redirect('/forum');
            }
        }

        $cache_time = $this->cache_time;
        $messages_on_page = $this->messages_on_page;

        $topic = ForumTopic::where(['id' => $topic_id, 'forum_id' => $forum_id])->first();
        if (!$topic) {
            return redirect("/forum");
        }
        $fixed_message = null;
        if ($topic->first_message_fixed) {
            $fixed_message = ForumMessage::where(['topic_id' => $topic_id])->orderBy('id', 'asc')->first();
        }

        $cache_tag = 'msg_' . $topic_id . '_' . $page;
        $total_tag = 'total_' . $topic_id . '_' . $page;
        $messages = Cache::get($cache_tag);
        if (!$messages) {
            $messages = ForumMessage::where(['topic_id' => $topic_id]);
            if ($fixed_message) {
                $messages = $messages->where('id', '!=', $fixed_message->id);
            }
            $messages = $messages->with('user')->orderBy('id', 'asc');
            $total = $messages->count();
            $messages = $messages->limit($messages_on_page)->offset(($page - 1) * $messages_on_page)->get();
            Cache::put($cache_tag, $messages, $cache_time);
            Cache::put($total_tag, $total, $cache_time);
        } else {
            $total = Cache::get($total_tag);
        }
        $paginator = new ForumPaginator([], $total, $messages_on_page, $page, [
            'path'  => ForumPaginator::resolveCurrentPath(),
            'forum_id' => $subforum->id,
            'topic_id' => $topic->id,
        ]);

        $show_pager = $total > $messages_on_page;
        $users = $messages->pluck('user')->unique()->filter();


        return view("pages.forum.topic", [
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
            return redirect("/forum");
        }
        $page = ceil( $message_index / $this->messages_on_page);
        return redirect("/forum/$forum_id-$topic_id-$page#$message_id");
    }

    public function redirectToLastMessage($forum_id, $topic_id) {
        $messages = ForumMessage::where(['topic_id' => $topic_id])->pluck('id');
        if ($messages === 0) {
            return redirect("/forum");
        }
        $message_id = $messages->last();
        $page = ceil( count($messages) / $this->messages_on_page);
        return redirect("/forum/$forum_id-$topic_id-$page#$message_id");
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
                'html' =>  view("blocks/forum_form", ['edit_id' => $message->id, 'topic_id' => $message->topic_id, 'content' => $original_bb])->render()
            ]
        ];
    }

    private function deleteCache($topic_id, $page) {
        $cache_tag = 'msg_' . $topic_id . '_' . $page;
        Cache::forget($cache_tag);
        $total_tag = 'total_' . $topic_id . '_' . $page;
        Cache::forget($total_tag);
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

    private function recalculateLastMessage($forum) {
        $topic_ids = ForumTopic::where(['forum_id' => $forum->id])->pluck('id');
        $last_message = ForumMessage::whereIn('topic_id', $topic_ids)->orderBy('id', 'desc')->first();
        $forum->last_username = $last_message->username;
        $forum->last_topic_id = $last_message->topic->id;
        $forum->last_topic_name = $last_message->topic->title;
        $forum->last_reply_at = $last_message->created_at;
        $forum->save();
    }

    public function deleteMessage() {
        $message_id = request()->input('message_id');
        $message = ForumMessage::find($message_id);
        if (!$message) {
            return ['status' => 0, 'text' => 'Сообщение не найдено'];
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
            $this->recalculateLastMessage($message->topic->forum);
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

    public function postMessage() {
        $user = auth()->user();
        if (!$user) {
            return ['status' => 0, 'text' => 'Вы не авторизованы'];
        }
        $topic = ForumTopic::find(request()->input('topic_id'));
        if (!$topic) {
            return ['status' => 0, 'text' => 'Тема не найдена'];
        }
        if ($topic->is_closed) {
            return ['status' => 0, 'text' => 'Тема закрыта'];
        }
        $subforum = Forum::find($topic->forum_id);
        if (!PermissionsHelper::allows('frcloset') && (!PermissionsHelper::checkSubforumAccess('can_post', $subforum) || !PermissionsHelper::allows('frreply'))) {
            return ['status' => 0, 'text' => 'У вас нет прав для отправки сообщений в эту тему'];
        }
        $content = request()->input('message');
        if ($content == "") {
            return ['status' => 0, 'text' => 'Введите сообщение'];
        }

        $content = BBCodesHelper::BBToHTML($content);

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
            'ip' => request()->ip(),
            'questionnaire' => '',
            'user_id' => $user->id,
        ]);
        $message_obj->save();
        $subforum->last_username = $user->username;
        $subforum->last_topic_id = $topic->id;
        $subforum->last_topic_name = $topic->title;
        $subforum->last_reply_at = Carbon::now();
        $subforum->save();
        $messages = ForumMessage::where(['topic_id' => $topic->id])->get();
        $page = ceil(count($messages) / $this->messages_on_page);
        $this->deleteCache($topic->id, $page);

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
}
