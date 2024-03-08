<?php

namespace App\Console\Commands;

use App\Article;
use App\Forum;
use App\ForumMessage;
use App\ForumTopic;
use App\Helpers\CSVHelper;
use Carbon\Carbon;
use Illuminate\Console\Command;

class recalculateForumLastMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'forum:recalculate';

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
        $topics = ForumTopic::all();
        foreach ($topics as $topic) {
            $last_message = ForumMessage::where(['topic_id' => $topic->id])->orderBy('id', 'desc')->first();
            if (!$last_message) {
                echo "Topic: ".$topic->id." messages not found".PHP_EOL;
            } else {
                $topic->topic_last_username = $last_message->username;
                $topic->last_reply_at = Carbon::createFromTimestamp($last_message->created_at_ts);
                echo "Topic: " . $topic->title . " Last message from: " . $topic->topic_last_username . " at " . $topic->last_reply_at.PHP_EOL;
                $topic->save();
            }
        }
    }
}
