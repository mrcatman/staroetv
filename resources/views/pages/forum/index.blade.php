@extends('layouts.default')
@section('content')
    <div class="forum-page">
        @foreach ($forums as $forum)
        <div class="forum-section">
            <div class="forum-section__title">{{$forum->title}}</div>
            <div class="forum-section__children">
                @foreach ($forum->subforums as $subforum)
                    <div class="forum @if($subforum->is_closed) forum--closed @endif">
                        <a href="/forum/{{$subforum->id}}" class="forum__top">
                            <div class="forum__info"> 
                                <div class="forum__title">
                                    {{$subforum->title}}
                                </div>
                                @if ($subforum->description != "")
                                <div class="forum__description">
                                    {{$subforum->description}}
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
                        </a>
                        <div class="forum__last-topic">
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
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
@endsection
