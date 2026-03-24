<?php

namespace App\Console\Commands;

use App\Models\Forum;
use Illuminate\Console\Command;

class UpdateLastTopics extends Command
{
    protected $signature = 'forum:update-last-topics';

    public function handle()
    {
        $forums = Forum::all();
        foreach ($forums as $forum) {
            $forum->updateLastTopic();
            echo 'Forum: ' . $forum->title . '. Last topic: '.$forum->last_topic_name . PHP_EOL;
        }
    }
}
