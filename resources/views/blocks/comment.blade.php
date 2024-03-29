<div class="comment" data-id="{{$comment->id}}">
    <div class="comment__inner">
        <div class="comment__texts">
            <div class="comment__top-container">
                @if ($comment->user && $comment->user->avatar) <img class="comment__avatar" src="{{$comment->user->avatar->url}}" /> @endif
                <div class="comment__top">
                    <a @if ($comment->user) href="{{$comment->user->url}}" @endif class="comment__username">{{$comment->username}}</a>
                    <span class="comment__date">{{$comment->created_at}}</span>
                    @if (isset($show_link) && $show_link)<a target="_blank" href="{{$comment->url}}" class="comment__material-link">[Материал]</a>@endif
                    @if (!isset($show_link) || !$show_link)
                        @if ($comment->can_edit)<a class="comment__edit">[Редактировать]</a>@endif
                        @if ($comment->can_delete)<a class="comment__delete">[Удалить]</a>@endif
                    @endif
                    <div class="comment__rating">
                        @if (\App\Helpers\PermissionsHelper::allows("comrate"))
                            <span class="comment__rating__button comment__rating__button--plus">
                                <i class="fa fa-chevron-up"></i>
                            </span>
                        @endif
                        <span class="comment__rating__container">
                            <span class="comment__rating__number @if($comment->total_rating > 0) comment__rating__number--positive @elseif ($comment->total_rating < 0) comment__rating__number--negative @endif">{{$comment->total_rating}}</span>
                        </span>
                        @if (\App\Helpers\PermissionsHelper::allows("comrate"))
                          <span class="comment__rating__button comment__rating__button--minus">
                              <i class="fa fa-chevron-down"></i>
                          </span>
                        @endif
                    </div>
                </div>
            </div>
            @php($tag = isset($go_to) && $go_to ? "a" : "div")
            <{{$tag}} @if (isset($go_to) && $go_to) href="{{$comment->url}}?comment_id={{$comment->id}}#comments_block" @endif class="comment__text @if($comment->rating < -5) comment__text--negative @endif">
                {!! $comment->text !!}
            </{{$tag}}>
            <div class="comment__original-text" style="display: none">
                {!! $comment->original_text !!}
            </div>
            @if (!isset($show_link) || !$show_link)
            @if (\App\Helpers\PermissionsHelper::allows("comreply"))
                <a class="comment__reply">Ответить</a>
            @endif
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
