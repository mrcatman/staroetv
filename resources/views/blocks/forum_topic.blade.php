<div class="forum @if($topic->is_closed) forum--closed @endif @if($topic->is_fixed) forum--fixed @endif">
    <a href="/forum/{{$topic->forum_id}}-{{$topic->id}}-1" class="forum__top">
        <div class="forum__info">
            <div class="forum__title">
                {{$topic->title}}
            </div>
            @if ($topic->description != "")
            <div class="forum__description">
                {{$topic->description}}
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
    </a>
    <div class="forum__last-topic">
        <a href="" class="forum__last-topic__text">
            Последнее обновление:
        </a>
        <div class="forum__last-topic__info">
            <a  class="forum__last-topic__date">
                {{$topic->last_reply_at}}
            </a>
            <span class="forum__last-topic__username">
                Сообщение от: <a href="/index/8-0-{{$topic->topic_last_username}}" class="forum__last-topic__username__text">{{$topic->topic_last_username}}</a>
            </span>
        </div>
    </div>
</div>
