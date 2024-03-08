<div class="forum @if($topic->is_closed) forum--closed @endif @if($topic->is_fixed) forum--fixed @endif @if(!$topic->is_read) forum--unread @endif">
    <div class="forum__top">
        <div class="forum__info">
            <a href="/forum/{{$topic->forum_id}}-{{$topic->id}}-0-17-1" class="forum__title">
                @if ($topic->is_closed)
                    <div class="forum__locked">
                        <i class="fa fa-lock"></i>
                    </div>
                @endif
                @if (isset($search) && $search)
                   {!! \App\Helpers\HighlightHelper::highlight($topic->title, $search, true) !!}
                @else
                   {{ $topic->title }}
                @endif
            </a>
            @if ($topic->description != "")
            <a href="/forum/{{$topic->forum_id}}-{{$topic->id}}-1" class="forum__description">
                @if (isset($search) && $search)
                    {!! \App\Helpers\HighlightHelper::highlight($topic->description, $search, true) !!}
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
                <div class="forum__number">{{$topic->answers_count}}</div>
                <div class="forum__number__description">ответов</div>
            </div>
            <div class="forum__number__container forum__number__container--topics">
                <div class="forum__number">{{$topic->views_count}}</div>
                <div class="forum__number__description">просмотров</div>
            </div>
        </div>
    </div>
    <div class="forum__last-topic">
        <a href="" class="forum__last-topic__text">
            Последнее обновление:
        </a>
        <div class="forum__last-topic__info">
            <a class="forum__last-topic__date">
                {{$topic->last_reply_at}}
            </a>
            <span class="forum__last-topic__username">
                Сообщение от: <a href="/index/8-0-{{$topic->topic_last_username}}" class="forum__last-topic__username__text">{{$topic->topic_last_username}}</a>
            </span>
        </div>
    </div>
</div>
