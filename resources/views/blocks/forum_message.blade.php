@if (!isset($inner) || !$inner)
<div id="{{$message->id}}" class="forum-message @if ($fixed) forum-message--fixed @endif" data-id="{{$message->id}}">
@endif
    <div class="forum-message__top">
        <div class="forum-message__user-info">
            <a @if ($message->user) href="{{$message->user->url}}"  @endif  class="forum-message__username">
                {{$message->username}}
            </a>
            @if ($message->user && $message->user->avatar)
                <img class="forum-message__avatar" src="{{$message->user->avatar->url}}"/>
            @endif
            <div class="forum-message__mobile-left">
                @if ($message->user && $message->user->avatar)
                    <img class="forum-message__avatar" src="{{$message->user->avatar->url}}"/>
                @endif
                <div class="forum-message__mobile-left__texts">
                    <a @if ($message->user) href="{{$message->user->url}}"  @endif  class="forum-message__username">
                        {{$message->username}}
                    </a>
                    <div class="forum-message__date">
                        {{$message->created_at}}
                        <a class="forum-message__link" target="_blank" href="/forum/0-{{$message->id}}">[ссылка]</a>
                    </div>
                </div>
            </div>
            <div class="forum-message__mobile-menu">
                <span class="button button--light button--dropdown" >
                    <span class="button--dropdown__text">Действия</span>
                    <span class="button--dropdown__icon">
                        <i class="fa fa-chevron-down"></i>
                    </span>
                    <div class="button--dropdown__list">
                         @if ($message->user)
                            <a class="button--dropdown__list__item forum-message__show-profile" data-message-id="{{$message->id}}">Профиль</a>
                        @endif
                        @if ($message->can_edit)
                            <a class="button--dropdown__list__item forum-message__edit">Редактировать</a>
                            <a class="button--dropdown__list__item forum-message__delete">Удалить</a>
                        @endif
                        @auth
                            <a onclick="bb.insertQuote({{$message->id}},'{{$message->username}}');" onmouseover="bb.getSelection()" class="button--dropdown__list__item forum-message__quote">Цитата</a>
                        @endauth
                    </div>
                </span>
             </div>
            <div class="forum-message__user-texts">
                @if ($message->user && $message->user->user_comment)
                    <div class="forum-message__user-comment">{{$message->user->user_comment}}</div>
                @endif
                @if ($message->user)
                    <div class="forum-message__group-icon-container">
                        {!! $message->user->group_icon !!}
                    </div>
                @endif
                @if ($message->user)
                    <span class="forum-message__reputation">
                        Репутация:
                        <a data-user-id="{{$message->user->id}}" class="forum-message__reputation__number">
                        {{$message->user->reputation_number}}
                        </a>
                        @if ($message->user->can_change_reputation)
                        <a data-user-id="{{$message->user->id}}" class="forum-message__reputation__change">
                        ±
                        </a>
                        @endif
                    </span>
                @endif
                @if ($message->user)
                   <span class="forum-message__count">
                        Сообщений:
                        <strong>{{$message->user->forum_messages_count}}</strong>
                    </span>
                @endif
                @if ($message->user)
                    <span class="forum-message__user-awards">
                        Наград:
                         <a data-user-id="{{$message->user->id}}" class="forum-message__awards__number">
                            {{count($message->user->awards)}}
                         </a>
                    </span>
                @endif
                @if ($message->user)
                    <span class="forum-message__user-ban-level">
                        Замечания:
                        <a data-user-id="{{$message->user->id}}" class="forum-message__warnings__number">
                            {{$message->user->ban_level}}%
                         </a>
                    </span>
                @endif
            </div>
        </div>
    </div>
    <div class="forum-message__right">
        <div class="forum-message__date">
            {{$message->created_at}}
            <a class="forum-message__link" target="_blank" href="/forum/0-{{$message->id}}">[ссылка]</a>
        </div>
        <div class="forum-message__content">
            @if (isset($highlight) && $highlight)
            {!! \App\Helpers\HighlightHelper::highlight($message->content, $highlight) !!}
            @else
            {!! $message->content !!}
            @endif
            @if ($message->edited_by)
            <div class="forum-message__edited-by">
                Сообщение отредактировал <span class="forum-message__edited-by__username">{{$message->edited_by}}</span> - {{$message->edited_at}}
            </div>
            @endif
                @if (request()->has('test'))@json($message->ip)@endif
        </div>
        <div class="forum-message__edit-form" style="display:none"></div>
        @if ($message->user && $message->user->signature != "")
            <div class="forum-message__signature">
                {!! $message->user->signature !!}
            </div>
        @endif
        <div class="forum-message__actions">
            <div class="forum-message__actions__left">
                @if ($message->user)
                <a class="button button--light" target="_blank" href="{{$message->user->url}}"><i class="fa fa-user"></i>Профиль</a>
                @endif
            </div>
            <div class="forum-message__actions__right">
                <div class="buttons-row">
                    @if ($message->can_edit)
                        <a class="button button--light forum-message__edit"><i class="fa fa-edit"></i><span class="button__text">Редактировать</span></a>
                    @endif
                    @if ($message->can_edit)
                        <a class="button button--light forum-message__delete"><i class="fa fa-trash"></i><span class="button__text">Удалить</span></a>
                    @endif
                    @auth
                        <a onclick="bb.insertQuote({{$message->id}},'{{$message->username}}');" onmouseover="bb.getSelection()" class="button button--light forum-message__quote"><i class="fa fa-quote-right"></i><span class="button__text">Цитата</span></a>
                    @endauth
                </div>

            </div>
        </div>
    </div>
@if (!isset($inner) || !$inner)
</div>
@endif
