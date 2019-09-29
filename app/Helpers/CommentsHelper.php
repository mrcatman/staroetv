<?php
namespace App\Helpers;
use App\Comment;

class CommentsHelper {

    public static function getPage($conditions, $page = 1) {
        $count = 10;
        $comments_with_parent = Comment::where($conditions)->where('parent_id', '>', '0')->get();
        $comments_by_parent = [];
        foreach ($comments_with_parent as $comment) {
            if (!isset($comments_by_parent[$comment->parent_id])) {
                $comments_by_parent[$comment->parent_id] = [];
            }
            $comments_by_parent[$comment->parent_id][] = $comment;
        }
        $comments = Comment::where($conditions)->where(function($q) {
            $q->where(['parent_id' => 0]);
            $q->orWhereNull('parent_id');
        })->orderBy('id', 'desc')->paginate($count);
        $comments->getCollection()->transform(function($comment) use ($comments_by_parent) {
            $comment->children = self::getChildren($comment, $comments_by_parent);
            return $comment;
        });
        return $comments;
    }

    protected static function getChildren($comment, $comments_by_parent) {
        $children = isset($comments_by_parent[$comment->id]) ? $comments_by_parent[$comment->id] : [];
        foreach ($children as $child) {
            $child->children = self::getChildren($child, $comments_by_parent);
        }
        return $children;
    }

}