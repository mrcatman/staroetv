<?php
namespace App\Helpers;

use Illuminate\Pagination\LengthAwarePaginator;

class ForumPaginator extends LengthAwarePaginator {

    public function url($page) {
        if (isset($this->options['topic_id'])) {
            $link = "/forum/".$this->options['forum_id']."-".$this->options['topic_id']."-".$page;
        } else {
            if (isset($this->options['forum_id'])) {
                $link = "/forum/" . $this->options['forum_id'] . "-0-" . $page;
            } else {
                $link = "/forum?page=".$page;
            }
        }
        if (request()->has('s')) {
            if (!isset($this->options['forum_id'])) {
                $link .= "&s=" . request()->input('s');
            } else {
                $link .= "?s=" . request()->input('s');
            }
            if (request()->has('type')) {
                $link.= "&type=".request()->input('type');
            }
        }

        return $link;
    }
}