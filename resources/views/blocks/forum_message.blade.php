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
            <div class="forum-message__user-texts">
                @if ($message->user && $message->user->user_comment)
                    <div class="forum-message__user-comment">{{$message->user->user_comment}}</div>
                @endif
                @if ($message->user)
                    <img class="forum-message__group-icon" src="{{$message->user->group_icon}}"/>
                @endif
                @if ($message->user)
                    @if (\App\Helpers\PermissionsHelper::allows("readrep"))
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
        </div>
        <div class="forum-message__content">
            {!! $message->content !!}
            @if ($message->edited_by)
            <div class="forum-message__edited-by">
                Сообщение отредактировал <span class="forum-message__edited-by__username">{{$message->edited_by}}</span> - {{$message->edited_at}}
            </div>
            @endif
        </div>
        <div class="forum-message__edit-form" style="display:none"></div>
        @if ($message->user && $message->user->signature != "")
            <div class="forum-message__signature">
                {!! $message->user->signature !!}
            </div>
        @endif
        <div class="forum-message__actions">
            <div class="forum-message__actions__left">
                <a class="button button--light" target="_blank" href="{{$message->user->url}}">Профиль</a>
            </div>
            <div class="forum-message__actions__right">
                @if ($message->can_edit)
                <a class="button button--light forum-message__edit">Редактировать</a>
                @endif
                @if ($message->can_edit)
                <a class="button button--light forum-message__delete">Удалить</a>
                @endif
                <a onclick="bb.insertQuote({{$message->id}},'{{$message->username}}');" onmouseover="bb.getSelection()" class="button button--light forum-message__quote">Цитата</a>
            </div>
        </div>
    </div>
@if (!isset($inner) || !$inner)
</div>
@endif