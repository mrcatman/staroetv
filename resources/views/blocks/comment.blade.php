<div class="comment" data-id="{{$comment->id}}">
    <div class="comment__inner">
        @if ($comment->user && $comment->user->avatar) <img class="comment__avatar" src="{{$comment->user->avatar->url}}" /> @endif
        <div class="comment__texts @if($comment->user && $comment->user->avatar) comment__texts--with-avatar @endif">
            <div class="comment__top">
                <a @if ($comment->user) href="{{$comment->user->url}}" @endif class="comment__username">{{$comment->username}}</a>
                <span class="comment__date">{{$comment->created_at}}</span>
                @if ($comment->can_edit)<a class="comment__edit">[Редактировать]</a>@endif
                @if ($comment->can_delete)<a class="comment__delete">[Удалить]</a>@endif
                <div class="comment__rating">
                    @if (\App\Helpers\PermissionsHelper::allows("comrate"))
                    <span class="comment__rating__button comment__rating__button--plus">+</span>
                    @endif
                    <span class="comment__rating__number @if($comment->rating > 0) comment__rating__number--positive @elseif ($comment->rating < 0) comment__rating__number--negative @endif">{{$comment->rating}}</span>
                    @if (\App\Helpers\PermissionsHelper::allows("comrate"))
                    <span class="comment__rating__button comment__rating__button--minus">-</span>
                    @endif
                </div>
            </div>
            <div class="comment__text @if($comment->rating < -5) comment__text--negative @endif">
                {!! $comment->text !!}
            </div>
            <div class="comment__original-text" style="display: none">
                {!! $comment->original_text !!}
            </div>
            @if (\App\Helpers\PermissionsHelper::allows("comreply"))
                <a class="comment__reply">Ответить</a>
            @endif
        </div>
    </div>
    <div class="comment__children">
        @if (isset($comment->children) && count($comment->children) > 0)
            @foreach ($comment->children as $child)
                @include('blocks/comment', ['comment' => $child])
            @endforeach
        @endif
    </div>

</div>