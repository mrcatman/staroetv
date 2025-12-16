<?php

namespace App\Http\Controllers;

use App\Constants\Actions;
use App\Constants\MaterialTypes;
use App\Helpers\ActionsLogHelper;
use App\Helpers\BBCodesHelper;
use App\Helpers\PermissionsHelper;

use App\Models\Comment;
use App\Models\CommentRating;
use App\Models\Record;
use App\Models\User;

use App\Notifications\NewCommentReply;
use App\Notifications\NewMaterialComment;

class CommentsController extends Controller {


    public function latest()
    {
        $comments = Comment::orderBy('id', 'desc')->where('material_type', '!=', '3')->paginate(24);
        return view("pages.users.comments", [
            'comments' => $comments,
            'user' => null,
        ]);
    }

    public function user($id) {
        $user = User::find($id);
        if (!$user) {
            return redirect(route('index'));
        }
        $comments = Comment::where(['user_id' => $id])->orderBy('id', 'desc')->paginate(30);

        return view("pages.users.comments", [
            'comments' => $comments,
            'user' => $user,
        ]);
    }

    public function ajax() {
        $conditions = request()->input('conditions');
        $data = [
            'html' => view("blocks.comments.list", ['ajax' => true, 'page' =>  request()->input('page', 1), 'conditions' => $conditions])->render()
        ];
        if (request()->has('count')) {
            $data['count'] = Comment::where($conditions)->count();
        }
        return [
            'status' => 1,
            'data' => $data
        ];
    }

    public function add()
    {
        $user = auth()->user();

        if (!$user || !PermissionsHelper::allows("comadd") || PermissionsHelper::isBanned()) {
            return [
                'status' => 0,
                'text' => 'Вы не можете оставлять комментарии'
            ];
        }
        if (!request()->has('material_type') || !request()->has('material_id')) {
            return [
                'status' => 0,
                'text' => 'Неверные данные'
            ];
        }

        $material_type = request()->input('material_type');
        $material_id = request()->input('material_id');

        $allowed_material_types = array_intersect_key(MaterialTypes::LIST,  [
            MaterialTypes::TYPE_CHANNELS => null,
            MaterialTypes::TYPE_ARTICLES => null,
            MaterialTypes::TYPE_NEWS => null,
            MaterialTypes::TYPE_BLOG => null,
            MaterialTypes::TYPE_RECORDS => null,
            MaterialTypes::TYPE_TELETEXT => null,
            MaterialTypes::TYPE_PROGRAMS => null,
            MaterialTypes::TYPE_INTERPROGRAM => null,
            MaterialTypes::TYPE_HISTORY_EVENTS => null
        ]);

        if (!isset($allowed_material_types[$material_type])) {
            return [
                'status' => 0,
                'text' => 'Неверные данные'
            ];
        }

        if ($material_type == MaterialTypes::TYPE_RECORDS) {
            $material = Record::where(['ucoz_id' => $material_id])->first();
        } else {
            $material = $allowed_material_types[$material_type]::find($material_id);
        }

        if (!$material) {
            return [
                'status' => 0,
                'text' => 'Материал не найден'
            ];
        }

        $original_text = request()->input('message', '');
        if (trim($original_text) == '') {
            return [
                'status' => 0,
                'text' => 'Не заполнено поле "Комментарий"'
            ];
        }

        $text = BBCodesHelper::BBToHTML($original_text);

        $comment = new Comment([
            'material_type' => $material_type,
            'material_id' => $material_id,
            'username' => $user->username,
            'name' => '',
            'email' => '',
            'ip_address' => request()->header('x-real-ip'),
            'text' => $text,
            'rating' => 0,
            'user_id' => $user->id,
            'original_text' => $original_text,
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

        ActionsLogHelper::create($comment, Actions::Create);

        if ($parent && $parent->user) {
            $parent->user->notify(new NewCommentReply($parent, $comment));
        }

        if ($material->user && $material->user->id != $user->id) {
            $material->user->notify(new NewMaterialComment($comment));
        }

        return [
            'status' => 1,
            'text' => 'Комментарий добавлен',
            'data' => [
                'dom' => [
                    [
                        'prepend_to' => $selector,
                        'html' => view("blocks.comments.item", ['ajax' => true, 'comment' => $comment])->render()
                    ]
                ]
            ]
        ];
    }

    public function edit()
    {
        $id = request()->input('id');
        $comment = Comment::find($id);
        if ($comment && $comment->can_edit && !PermissionsHelper::isBanned()) {
            if (request()->has('message') && request()->input('message') != "") {
                $original_text = request()->input('message');
                $text = BBCodesHelper::BBToHTML($original_text);
                $comment->original_text = $original_text;
                $comment->text = $text;
                $selector = '.comment[data-id="' . $id . '"]';

                ActionsLogHelper::create($comment, Actions::Update);

                return [
                    'status' => 1,
                    'text' => 'Комментарий сохранен',
                    'data' => [
                        'dom' => [
                            [
                                'replace' => $selector,
                                'html' => view("blocks.comments.item", ['ajax' => true, 'comment' => $comment])->render()
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

    public function delete()
    {
        $id = request()->input('id');
        $comment = Comment::find($id);
        if ($comment && $comment->can_delete && !PermissionsHelper::isBanned()) {
            ActionsLogHelper::create($comment, Actions::Delete);
            return [
                'status' => 1,
                'text' => 'Комментарий удалён',
                'data' => [
                    'dom' => [
                        [
                            'replace' => ".comment[data-id=" . $comment->id . "]",
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
        if ($rating->weight != $weight) {
            $rating->weight = $weight;
            $rating->save();
        } else {
            $rating->delete();
        }

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

    public function new()
    {
        $comments = Comment::orderBy('id', 'desc')->where('material_type', '!=', '3')->paginate(24);
        return view("pages.users.comments", [
            'comments' => $comments,
            'user' => null,
        ]);
    }


}
