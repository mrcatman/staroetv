<div class="forum @if($subforum->is_closed) forum--closed @endif @if(!$subforum->is_read) forum--unread @endif">
    <div class="forum__top">
        <div class="forum__info">

            <a href="{{route('forum.subforums.show', $subforum->id)}}" class="forum__title">
                @if ($subforum->is_closed)
                    <div class="forum__param">
                        <i class="fa fa-lock"></i>
                    </div>
                @endif
                {{$subforum->title}}
            </a>
            @if ($subforum->description != "")
                <a href="{{route('forum.subforums.show', $subforum->id)}}" class="forum__description">
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
                <div class="forum__number">
                    <span class="forum__number__icon"><i class="fa-regular fa-comments"></i></span>
                    {{$subforum->topics_count}}
                </div>
                <div class="forum__number__description">тем</div>
            </div>
        </div>
    </div>
    <div class="forum__last-topic">
        @if ($subforum->last_topic_id)
        <a href="{{route('forum.topics.show-last-message', [$subforum->id, $subforum->last_topic_id])}}" class="forum__last-topic__text">
            Последнее обновление:
        </a>
        <div class="forum__last-topic__info">

            <a href="{{route('forum.topics.show-last-message', [$subforum->id, $subforum->last_topic_id])}}" class="forum__last-topic__name">
                <i class="fa-regular fa-message"></i>
                {{$subforum->last_topic_name}}
            </a>
            <span class="forum__last-topic__username">
                <span class="forum__last-topic__date">
                  <i class="fa-regular fa-clock"></i>
                    {{$subforum->last_reply_at}}
                </span>
                 <a href="{{route('users.show-by-username', $subforum->last_username)}}" class="forum__last-topic__username__text">
                    <i class="fa-regular fa-user"></i>
                     {{$subforum->last_username}}
                </a>
            </span>
        </div>
        @endif
    </div>

</div>
