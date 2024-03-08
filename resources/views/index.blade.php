@extends('layouts.default')
@section('head')
    <meta property="og:image" content="https://staroetv.su/img/og_cover.png">
@endsection
@section('content')
    <div class="inner-page__content main-page">
        <div class="main-page__intro">
            <video class="main-page__intro__video" autoplay muted loop>
                <source src="/splashscreen/videos/noise.mp4"/>
            </video>
            <div class="main-page__intro__overlay"></div>
            <div class="main-page__intro__text"><strong>Старый Телевизор</strong> - архив старых теле- и радиозаписей</div>
        </div>
        <!--
        <div class="box">
            <div class="box__inner ">
                <a style="text-decoration: none; " href="/news/2020-08-05-4397">
                    <h1 style="text-align: center; margin: .5em 0;font-size: 2.5em;color:var(--primary); ">Мы перезапустились</h1>
                </a>
            </div>
        </div>
        -->
        <div class="box">
            <div class="box__inner">
                <div class="main-page__site-description">
                    Наш сайт создан для тех, кто любит телевидение прошлого и интересуется телевидением настоящего. <br>
                    В нашем архиве вы можете найти записи старых телепередач <a href="/video/search?dates_range.end=479376360">времён раннего СССР</a>, <a href="/video/search?dates_range.start=479376360&dates_range.end=693738360">перестроечных лет</a>, <a href="/video/search?dates_range.start=693738360&dates_range.end=946631160">девяностых</a> и <a href="/video/search?dates_range.start=946631160">начала 2000-х</a> годов.<br>
                    Отдельное внимание уделено теме <a href="/video/graphics">телевизионного дизайна</a> и <a href="/video/commercials">рекламы</a>.<br><br>
                    Материалы сайта собираются силами <a href="/top-list/videos">сообщества</a>: как с просторов Интернета, так и благодаря собственным коллекциям.
                </div>
            </div>
        </div>
        <div class="row row--stretch">

            <div class="col col--2">
                <div class="box">
                    <a href="/video" class="box__heading">
                        <div class="box__heading__inner">
                            Последние пополнения телеархива
                        </div>
                    </a>
                    <div class="box__inner">
                        <div class="records-list records-list--small">
                            @foreach($records as $record)
                                @include('blocks/record', ['record' => $record])
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <!--
                <div class="box  @if ($in_this_day && count($in_this_day) > 0) @else box--stretch @endif">
                    <a href="/events" class="box__heading">
                        <div class="box__heading__inner">
                            Подборки
                        </div>
                    </a>
                    <div class="box__inner">
                        @foreach($events as $event)
                            @include('blocks/event', ['event' => $event])
                        @endforeach
                    </div>
                </div>
                -->
                @if ($last_viewed && count($last_viewed) > 0)
                        <div class="box">
                            <div class="box__heading">
                                <div class="box__heading__inner">
                                    Сейчас смотрят
                                </div>
                            </div>
                            <div class="box__inner">
                                @foreach($last_viewed as $last_viewed_record)
                                    @include('blocks/record', ['record' => $last_viewed_record])
                                @endforeach
                            </div>
                        </div>
                @endif
                @if ($in_this_day && count($in_this_day) > 0)
                    <div class="box">
                        <div class="box__heading">
                            <div class="box__heading__inner">
                                {{$date_text}} в истории
                            </div>
                        </div>
                        <div class="box__inner">
                            @foreach($in_this_day as $in_this_day_record)
                                @include('blocks/record', ['record' => $in_this_day_record])
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
        <div class="row">
            <div class="col">
                <div class="box">
                    <a href="/articles" class="box__heading">
                        <div class="box__heading__inner">
                            Публикации
                        </div>
                    </a>
                    <div class="box__inner box__inner--no-padding">
                        <div class="articles-list">
                            <div class="articles-list__block articles-list__block--big">
                                @foreach ($first_news as $first_news_item)
                                    <a href="{{$first_news_item->url}}" style="background-image:url({{$first_news_item->cover_url}})" class="article article--big">
                                        <div class="article__texts">
                                            <div class="article__title">{{$first_news_item->title}}</div>
                                            <div class="article__short-content" style="display:none;">{{$first_news_item->short_content}}</div>
                                        </div>
                                    </a>
                               @endforeach
                            </div>
                            <div class="articles-list__block articles-list__block--right">
                                @foreach ($news as $news_item)
                                @include('blocks/news', ['hide_tags' => true, 'show_cover' => false, 'news_item' => $news_item])
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row row--stretch">
            <div class="col">
                <div class="box box--stretch">
                    <a href="/forum" class="box__heading">
                        <div class="box__heading__inner">
                            Форум
                        </div>
                    </a>
                    <div class="box__inner box__inner--stretch-contents box__inner--no-padding forum-topics-list">
                            @foreach ($forum_topics as $forum_topic)
                            <a class="forum-topics-list__item" href="/forum/{{$forum_topic->forum_id}}-{{$forum_topic->id}}-0-17-1">
                                <div class="forum-topics-list__item__title">{{$forum_topic->title}}</div>
                                <div class="forum-topics-list__item__bottom">
                                    <div class="icon-blocks">
                                         <span class="icon-block">
                                            <i class="fa fa-clock"></i>
                                            <span class="icon-block__text">{{$forum_topic->last_reply_at}}</span>
                                        </span>
                                        <span class="icon-block">
                                           <i class="fa fa-user"></i>
                                           <span class="icon-block__text">{{$forum_topic->topic_last_username}}</span>
                                        </span>
                                    </div>
                                </div>
                            </a>
                            @endforeach
                    </div>
                </div>
            </div>
            @if (isset($comments))
            <div class="col">
                <div class="box box--stretch box--comments-main-page">
                    <a href="/new-comments" class="box__heading">
                        <div class="box__heading__inner">
                             Последние комментарии
                        </div>
                    </a>
                    <div class="box__inner">
                        <div class="comments">
                            @foreach ($comments as $comment)
                                @include('blocks/comment', ['go_to' => true, 'show_link' => true, 'comment' => $comment])
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @else
            <div class="col">
                @include('blocks/banner')
            </div>
            @endif
        </div>
        <div class="row">
            <div class="box">
                <div class="box__heading box__heading--small">
                    Сейчас на сайте
                </div>
                <div class="box__inner">
                    @php($index = 0)
                    @foreach ($users_on_site as $user)
                        @php ($last = !isset($users_on_site[$index + 1]))

                        <a target="_blank" href="{{$user->url}}" class="user-online" data-group-id="{{$user->group_id}}">{{$user->username}}</a>@if (!$last), @endif
                        @php ($index++)
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection
