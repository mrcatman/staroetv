<div class="forum @if($topic->is_closed) forum--closed @endif @if($topic->is_fixed) forum--fixed @endif @if(!$topic->is_read) forum--unread @endif">
    <div class="forum__top">
        <div class="forum__info">
            <a href="{{route('forum.topics.show', [$topic->forum_id, $topic->id])}}" class="forum__title">
                @if ($topic->is_closed)
                    <div class="forum__param">
                        <i class="fa fa-lock"></i>
                    </div>
                @endif
                    @if ($topic->is_poll)
                        <div class="forum__param">
                            <i class="fa fa-chart-bar"></i>
                        </div>
                    @endif
                @if (isset($search) && $search)
                   {!! \App\Helpers\HighlightHelper::highlight($topic->title, $search) !!}
                @else
                   {{ $topic->title }}
                @endif
            </a>
            @if ($topic->description != "")
            <a href="{{route('forum.topics.show', [$topic->forum_id, $topic->id])}}" class="forum__description">
                @if (isset($search) && $search)
                    {!! \App\Helpers\HighlightHelper::highlight($topic->description, $search) !!}
                @else
                    {{ $topic->description }}
                @endif
            </a>
            @endif
            @if (isset($topic->users))
                <div class="forum__users">
                    Сейчас смотрят:
                    @foreach ($topic->users as $user)
                        <a target="_blank" href="{{$user->url}}" class="user-online" data-group-id="{{$user->group_id}}">{{$user->username}}</a>
                    @endforeach
                </div>
            @endif
        </div>
        <div class="forum__numbers">
            <div class="forum__number__container forum__number__container--topics">
                <div class="forum__number">
                    <span class="forum__number__icon"><i class="fa-regular fa-comments"></i></span>
                    {{$topic->answers_count}}
                </div>
                <div class="forum__number__description">сообщений</div>
            </div>
            <div class="forum__number__container forum__number__container--topics">
                <div class="forum__number">
                    <span class="forum__number__icon"><i class="fa-regular fa-eye"></i></span>
                    {{$topic->views_count}}
                </div>
                <div class="forum__number__description">просмотров</div>
            </div>
        </div>
    </div>
    <div class="forum__last-topic">
        <span class="forum__last-topic__text">
            Последнее обновление:
        </span>
        <div class="forum__last-topic__info">
            <a href="{{route('forum.topics.show-last-message', [$topic->forum_id, $topic->id])}}" class="forum__last-topic__date forum__last-topic__message">
                <i class="fa fa-clock"></i>&nbsp;{{$topic->last_reply_at}} <i class="fa fa-chevron-right"></i>
            </a>
            <span class="forum__last-topic__username">
                <a href="{{route('users.show-by-username', $topic->topic_last_username)}}" class="forum__last-topic__username__text"><i class="fa fa-user"></i>&nbsp;{{$topic->topic_last_username}}</a>
            </span>
        </div>
    </div>
</div>
