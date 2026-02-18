<?php

namespace App\Console\Commands;

use App\Models\ForumTopic;

use Illuminate\Console\Command;

class RemoveEmptyTopics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'forum:remove-empty-topics';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove topics without messages';

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
        ForumTopic::withCount('messages')->chunk(100, function ($topics) {
            $topics->each(function ($topic) {
                if ($topic->messages_count == 0) {
                    $topic->delete();
                }
            });
        });
    }
}
