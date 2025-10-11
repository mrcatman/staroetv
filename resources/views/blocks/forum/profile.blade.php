<div class="forum-message__user-texts forum-message__user-texts--modal">
    <div class=" forum-message__user-texts--modal__left">
        @if ($message->user->avatar)
            <img class="forum-message__user-texts--modal__avatar" src="{{$message->user->avatar->url}}"/>
        @endif
        <a href="{{$message->user->url}}"   class="forum-message__user-texts--modal__username">
            {{$message->username}}
        </a>
    </div>
    <div class=" forum-message__user-texts--modal__right">
        <div class="forum-message__user-comment">{{$message->user->user_comment}}</div>
        <img class="forum-message__group-icon" src="{{$message->user->group_icon}}"/>
        <span class="forum-message__reputation">
            Репутация:
            <a data-user-id="{{$message->user->id}}" class="forum-message__reputation__number">{{$message->user->reputation_number}}</a>
            @if ($message->user->can_change_reputation)
                <a data-user-id="{{$message->user->id}}" class="forum-message__reputation__change">±</a>
            @endif
        </span>
        <span class="forum-message__user-awards">
            Наград: <a data-user-id="{{$message->user->id}}" class="forum-message__awards__number">{{count($message->user->awards)}}</a>
        </span>
        <span class="forum-message__user-ban-level">
            Замечания: <a data-user-id="{{$message->user->id}}" class="forum-message__warnings__number">{{$message->user->ban_level}}%</a>
        </span>
    </div>
</div>
