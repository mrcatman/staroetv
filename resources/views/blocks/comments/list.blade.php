@if (\App\Helpers\PermissionsHelper::allows("comread"))
    @if(!isset($lazyload))
        @php($lazyload = false)
    @endif
    @if (!$lazyload)
        @php($comments = \App\Helpers\CommentsHelper::getPage($conditions, $page))
    @else
        @php($comments = [])
    @endif
    @if (!$ajax)
        <div id="comments_block" class="box @if (isset ($class)) {{$class}} @else box--comments @endif">
            <div class="box__heading">
                <div class="box__heading__inner">
                    <i class="fa fa-comment"></i>
                    Комментарии&nbsp;
                    @if (!$lazyload)
                        <span class="box__heading__count">{{\App\Models\Comment::where($conditions)->count()}}</span>
                    @else
                        <span class="box__heading__count box--comments__loading">загрузка...</span>
                    @endif
                </div>
            </div>
            <div class="box__inner">
                <div class="comments @if($lazyload) comments--lazyload @endif"
                     data-conditions="{{json_encode($conditions)}}">

                    <div class="comments__form">
                        @if (!auth()->user())
                            <div class="comments__form__register">
                                <a class="button--login" href="{{route('login')}}">Войдите</a> или <a href="{{route('register')}}">зарегистрируйтесь</a>, чтобы
                                добавить комментарий
                            </div>
                        @else
                            @if (\App\Helpers\PermissionsHelper::allows("comadd"))
                                @include('blocks.comments.form', ['material_type' => $conditions['material_type'], 'material_id' => $conditions['material_id']])
                            @else
                                <div class="comments__form__register">
                                    Пользователи, находящиеся в вашей группе, не могут оставлять комментарии
                                </div>
                            @endif
                        @endif
                    </div>

                    <div class="comments__main">
                        @endif
                        <div class="comments__list @if (count($comments) == 0) comments__list--empty @endif">
                            @foreach ($comments as $comment)
                                @include('blocks.comments.item', ['comment' => $comment])
                            @endforeach
                        </div>
                        @if (!$lazyload)
                            @if($comments->lastPage() > 1)
                                <div class="comments__pager">
                                    {{ $comments->links() }}
                                </div>
                            @endif
                        @endif
                        <div class="comments__reply-form-container" id="reply_form_container"></div>
                        <div class="comments__edit-form-container" id="edit_form_container"></div>

                        @if (!$ajax)
                    </div>


                </div>
            </div>
        </div>
    @endif
@endif

