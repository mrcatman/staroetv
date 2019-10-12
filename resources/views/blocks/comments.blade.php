@if (\App\Helpers\PermissionsHelper::allows("comread"))
@php($comments = \App\Helpers\CommentsHelper::getPage($conditions, $page))
@if (!$ajax)
<div class="comments" data-conditions="{{json_encode($conditions)}}">
@endif
    <div class="comments__form">
        @if (!auth()->user())
        <div class="comments__form__register">
            <a href="/login">Войдите</a> или <a href="/register">зарегистрируйтесь</a>, чтобы добавить комментарий
        </div>
        @else
            @if (\App\Helpers\PermissionsHelper::allows("comadd"))
                @include('blocks/comments_form', ['material_type' => $conditions['material_type'], 'material_id' => $conditions['material_id']])
            @else
                <div class="comments__form__register">
                    Пользователи, находящиеся в вашей группе, не могут оставлять комментарии
                </div>
            @endif
        @endif
    </div>
    <div class="comments__list">
        @foreach ($comments as $comment)
            @include('blocks/comment', ['comment' => $comment])
        @endforeach
    </div>
    @if($comments->lastPage() > 1)
    <div class="comments__pages">
        {{ $comments->links() }}
    </div>
    @endif
    <div class="comments__reply-form-container" id="reply_form_container"></div>
    <div class="comments__edit-form-container" id="edit_form_container"></div>
@if (!$ajax)
</div>

@endif
@endif

