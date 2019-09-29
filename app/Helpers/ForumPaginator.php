<?php
namespace App\Helpers;

use Illuminate\Pagination\LengthAwarePaginator;

class ForumPaginator extends LengthAwarePaginator {

    public function url($page) {
        return "/forum/".$this->options['forum_id']."-".$this->options['topic_id']."-".$page;
    }
}