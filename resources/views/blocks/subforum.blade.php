<div class="forum @if($subforum->is_closed) forum--closed @endif @if(!$subforum->is_read) forum--unread @endif">
    <div class="forum__top">
        <div class="forum__info">
            @if ($subforum->is_closed)
                <div class="forum__locked">
                    <i class="fa fa-lock"></i>
                </div>
            @endif
            <a href="/forum/{{$subforum->id}}" class="forum__title">
                {{$subforum->title}}
            </a>
            @if ($subforum->description != "")
                <a href="/forum/{{$subforum->id}}" class="forum__description">
                    {{$subforum->description}}
                </a>
            @endif
            @if (isset($subforum->users))
                <div class="forum__users">
                    Сейчас смотрят:
                    @foreach ($subforum->users as $user)
                        <a target="_blank" href="{{$user->url}}" class="user-online" data-group-id="{{$user->group_id}}">{{$user->username}}</a>
                    @endforeach
                </div>
            @endif
        </div>
        <div class="forum__numbers">
            <div class="forum__number__container forum__number__container--topics">
                <div class="forum__number">{{$subforum->topics_count}}</div>
                <div class="forum__number__description">тем</div>
            </div>
            <div class="forum__number__container forum__number__container--topics">
                <div class="forum__number">{{$subforum->replies_count}}</div>
                <div class="forum__number__description">ответов</div>
            </div>
        </div>
    </div>
    <div class="forum__last-topic">
        @if ($subforum->last_topic_id)
        <a href="/forum/{{$subforum->id}}-{{$subforum->last_topic_id}}-0-17-1" class="forum__last-topic__text">
            Последнее обновление:
        </a>
        <div class="forum__last-topic__info">
            <a href="/forum/{{$subforum->id}}-{{$subforum->last_topic_id}}0-17-1" class="forum__last-topic__date">
                {{$subforum->last_reply_at}}
            </a>
            <a href="/forum/{{$subforum->id}}-{{$subforum->last_topic_id}}-0-17-1" class="forum__last-topic__name">
                {{$subforum->last_topic_name}}
            </a>
            <span class="forum__last-topic__username">
                Сообщение от: <a href="/index/8-0-{{$subforum->last_username}}" class="forum__last-topic__username__text">{{$subforum->last_username}}</a>
            </span>
        </div>
        @endif
    </div>

</div>
