<?php

namespace App\Console\Commands;

use App\Comment;
use App\CommentRating;
use App\Helpers\CSVHelper;
use Illuminate\Console\Command;

class importComments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'comments:import';

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
        $comments = CSVHelper::transform(public_path("data_new/comments.txt"), [
            'id', 'material_type', 'material_id', '', 'created_at', 'username', 'name', 'email', '', 'ip_address', 'text', '', 'user_id', 'parent_id',  'rating', 'rated_ids'
        ]);
        foreach ($comments as $comment) {
            $data = Comment::find($comment['id']);
            if (!$data) {
                $rated_ids = $comment['rated_ids'];
                unset($comment['rated_ids']);
                unset($comment['']);
                $comment_item = new Comment($comment);
                $comment_item->save();
                echo "Saved comment from user " . $comment['username'] . " to material " . $comment['material_id'] . " (material type " . $comment['material_type'] . ")" . PHP_EOL;
                if ($rated_ids != "") {
                    $rated_ids = explode(",", $rated_ids);
                    foreach ($rated_ids as $rated_id) {
                        $rating = new CommentRating([
                            'comment_id' => $comment_item->id,
                            'user_id' => $rated_id,
                        ]);
                        $rating->save();
                        echo "Saved rating from user " . $rated_id . " to comment " . $comment['material_id'] . " (material type " . $comment['material_type'] . ")" . PHP_EOL;
                    }
                }
            } else {
               // echo "Comment #".$comment['id']." already exists" . PHP_EOL;
            }
        }
       // file_put_contents(public_path("data/comments.json"), json_encode($comments, JSON_UNESCAPED_UNICODE));

    }
}
