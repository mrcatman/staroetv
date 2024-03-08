<?php

namespace App\Http\Controllers;

use App\Channel;
use App\ChannelName;
use App\Comment;
use App\CommentRating;
use App\ForumMessage;
use App\Helpers\BBCodesHelper;
use App\Helpers\CommentsHelper;
use App\Helpers\PermissionsHelper;
use App\Notifications\NewCommentReply;
use App\Notifications\NewForumReply;
use App\Program;
use App\Record;
use Carbon\Carbon;

class CommentsController extends Controller {

    public function ajax() {
        $conditions = request()->input('conditions');
        $data = [
            'html' => view("blocks/comments", ['ajax' => true, 'page' =>  request()->input('page', 1), 'conditions' => $conditions])->render()
        ];
        if (request()->has('count')) {
            $data['count'] = Comment::where($conditions)->count();
        }
        return [
            'status' => 1,
            'data' => $data
        ];
    }

    public function add() {
        if (PermissionsHelper::allows("comadd") && !PermissionsHelper::isBanned()) {
            if (request()->has('material_type') && request()->has('material_id')) {
                if (request()->has('message') && request()->input('message') != "") {
                    $text = BBCodesHelper::BBToHTML(request()->input('message'));
                    $comment = new Comment([
                        'material_type' => request()->input('material_type'),
                        'material_id' => request()->input('material_id'),
                        'username' => auth()->user()->username,
                        'name' => '',
                        'email' => '',
                        'ip_address' => request()->header('x-real-ip'),
                        'text' => $text,
                        'rating' => 0,
                        'user_id' => auth()->user()->id,
                        'original_text' => request()->input('message')
                    ]);
                    $parent = null;
                    $selector = '.comments__list';
                    if (request()->has('parent_id')) {
                        $parent_id = request()->input('parent_id');
                        if ((int)$parent_id > 0) {
                            $parent = Comment::find($parent_id);
                            if (!$parent) {
                                return [
                                    'status' => 0,
                                    'text' => 'Не найден родительский комментарий'
                                ];
                            }
                            $comment->parent_id = $parent_id;
                            $selector = '.comment[data-id="' . $parent_id . '"] .comment__children';
                        }
                    }
                    $comment->save();
                    if ($parent && $parent->user) {
                        $parent->user->notify(new NewCommentReply($parent, $comment));
                    }
                    return [
                        'status' => 1,
                        'text' => 'Комментарий добавлен',
                        'data' => [
                            'dom' => [
                                [
                                    'prepend_to' => $selector,
                                    'html' => view("blocks/comment", ['ajax' => true, 'comment' => $comment])->render()
                                ]
                            ]
                        ]
                    ];
                } else {
                    return [
                        'status' => 0,
                        'text' => 'Не заполнено поле "Комментарий"'
                    ];
                }
            } else {
                return [
                    'status' => 0,
                    'text' => 'Ошибка доступа'
                ];
            }
        } else {
            return [
                'status' => 0,
                'text' => 'Вы не можете оставлять комментарии'
            ];
        }
    }

    public function edit() {
        $id = request()->input('id');
        $comment = Comment::find($id);
        if ($comment && $comment->can_edit && !PermissionsHelper::isBanned()) {
              if (request()->has('message') && request()->input('message') != "") {
                    $original_text = request()->input('message');
                    $text = BBCodesHelper::BBToHTML($original_text);
                    $comment->original_text = $original_text;
                    $comment->text = $text;
                    $selector = '.comment[data-id="'.$id.'"]';
                    $comment->save();
                    return [
                        'status' => 1,
                        'text' => 'Комментарий сохранен',
                        'data' => [
                            'dom' => [
                                [
                                    'replace' => $selector,
                                    'html' => view("blocks/comment", ['ajax' => true, 'comment' => $comment])->render()
                                ]
                            ]
                        ]
                    ];
            } else {
                return [
                    'status' => 0,
                    'text' => 'Не заполнено поле "Комментарий"'
                ];
            }
        } else {
            return [
                'status' => 0,
                'text' => 'Вы не можете редактировать данный комментарий'
            ];
        }
    }

    public function delete() {
        $id = request()->input('id');
        $comment = Comment::find($id);
        if ($comment && $comment->can_delete && !PermissionsHelper::isBanned()) {
           $comment->delete();
           return [
               'status' => 1,
               'text' => 'Комментарий сохранен',
               'data' => [
                   'dom' => [
                       [
                           'replace' => ".comment[data-id=".$comment->id."]",
                           'html' => ""
                       ]
                   ]
               ]
           ];
        } else {
            return [
                'status' => 0,
                'text' => 'Вы не можете удалить данный комментарий'
            ];
        }
    }

    public function rating() {
        if (!PermissionsHelper::allows('comrate')) {
            return [
                'status' => 0,
                'text' => 'Вы не можете оценивать комментарии'
            ];
        }
        $comment = Comment::find(request()->input('comment_id'));
        if (!$comment) {
            return [
                'status' => 0,
                'text' => 'Комментарий не найден'
            ];
        }
        $weight = request()->input('weight');
        if ($weight != -1 && $weight != 1) {
            return [
                'status' => 0,
                'text' => 'Неверное значение веса рейтинга'
            ];
        }
        $rating = CommentRating::firstOrNew([
            'user_id' => auth()->user()->id,
            'comment_id' => $comment->id,
        ]);
        $rating->weight = $weight;
        $rating->save();

        $new_count = $comment->total_rating;

        $class = $new_count > 0 ? "comment__rating__number--positive" : ($new_count < 0 ? "comment__rating__number--negative" : "");
        $html = "<span class='comment__rating__number $class'>$new_count</span>";
        return [
            'status' => 1,
            'text' => 'Комментарий сохранен',
            'data' => [
                'dom' => [
                    [
                        'replace' => ".comment[data-id=".$comment->id."] > .comment__inner .comment__rating__container",
                        'html' => $html
                    ]
                ]
            ]
        ];
    }

    public function getOriginal($id) {
        $comment = Comment::find($id);
        $text = BBCodesHelper::HTMLToBB($comment->original_text);
        dd($text);
    }


}
