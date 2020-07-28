<?php

namespace App\Console\Commands;

use App\Article;
use App\Forum;
use App\ForumMessage;
use App\ForumTopic;
use App\Helpers\CSVHelper;
use Carbon\Carbon;
use Illuminate\Console\Command;

class importForum extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'forum:import';

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
        if (false) {
            echo "Parsing sections" . PHP_EOL;
            $sections = CSVHelper::transform(public_path("data/fr_fr.csv"), [
                'id', 'parent_id', '', '', 'created_at', 'title', 'description', 'state', '', 'topics_count', 'replies_count', 'last_reply_at', 'can_create_topics', 'can_post', '', 'last_username', 'last_topic_id', 'last_topic_name', '', 'can_view', '', '', ''
            ], false);
            // $messages = [];
            foreach ($sections as $forum) {
                unset($forum['']);
                $created_at = $forum['created_at'];
                unset($forum['created_at']);
                $last_reply_at = null;
                if (isset($forum['last_reply_at'])) {
                    $last_reply_at = $forum['last_reply_at'];
                    unset($forum['last_reply_at']);
                }
                $forum_obj = Forum::find($forum['id']);
                if (!$forum_obj) {
                    $forum_obj = new Forum($forum);
                } else {
                    $forum_obj->fill($forum);
                }
                $forum_obj->created_at = Carbon::createFromTimestamp($created_at);
                if ($last_reply_at) {
                    $forum_obj->last_reply_at = Carbon::createFromTimestamp($last_reply_at);
                }
                $forum_obj->save();
                echo "Saved forum " . $forum['title'] . PHP_EOL;
            }
            echo "Parsing topics" . PHP_EOL;
            $topics = CSVHelper::transform(public_path("data/forum.csv"), [
                'id', 'forum_id', 'is_poll', 'is_fixed', 'last_reply_at', 'is_closed', 'answers_count', 'views_count', 'title', 'description', 'topic_starter_username', '', 'topic_last_username', '', 'first_message_fixed', 'topic_starter_id', ''
            ], false);
            foreach ($topics as $topic) {
                unset($topic['']);
                $last_reply_at = $topic['last_reply_at'];
                if ($last_reply_at > 0) {
                    unset($topic['last_reply_at']);
                    $topic_obj = new ForumTopic($topic);
                    $topic_obj->last_reply_at = Carbon::createFromTimestamp($last_reply_at);
                    $topic_obj->save();
                    echo "Saved topic " . $topic['title'] . PHP_EOL;
                }
            }
        }
        echo "Parsing messages".PHP_EOL;
        $messages = CSVHelper::transform(public_path("data/forump.csv"), [
            'id', 'topic_id', 'created_at', 'is_first', 'content', '', 'username', '', 'edited_by', 'edited_at', '', '', 'ip', '', 'questionnaire', 'user_id', '', ''
        ], false);

        foreach ($messages as $message) {
            unset($message['']);
            $created_at = $message['created_at'];
            unset($message['created_at']);
            $edited_at = $message['edited_at'];
            unset($message['edited_at']);
            $message_obj = new ForumMessage($message);
            $message_obj->created_at = Carbon::createFromTimestamp($created_at);
            $message_obj->edited_at = Carbon::createFromTimestamp($edited_at);
            $message_obj->save();
            echo "Saved message by user ".$message['username']." with id: ".$message['id'].PHP_EOL;
        }

    }
}
