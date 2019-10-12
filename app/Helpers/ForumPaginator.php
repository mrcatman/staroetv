<?php
namespace App\Helpers;

use Illuminate\Pagination\LengthAwarePaginator;

class ForumPaginator extends LengthAwarePaginator {

    public function url($page) {
        if (isset($this->options['topic_id'])) {
            $link = "/forum/".$this->options['forum_id']."-".$this->options['topic_id']."-".$page;
        } else {
            $link = "/forum/" . $this->options['forum_id'] . "-0-" . $page;
        }
        if (request()->has('s')) {
            $link.= "?s=".request()->input('s');
        }
        return $link;
    }
}