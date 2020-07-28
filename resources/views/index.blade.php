@extends('layouts.default')
@section('content')
    <div class="inner-page__content main-page">
        <div class="main-page__intro">
            <video class="main-page__intro__video" autoplay muted loop>
                <source src="/splashscreen/videos/noise.mp4"/>
            </video>
            <div class="main-page__intro__overlay"></div>
            <div class="main-page__intro__text"><strong>Старый Телевизор</strong> - архив старых теле- и радиозаписей</div>
        </div>

        <div class="box">
            <div class="box__inner">
                <div class="main-page__site-description">
                    Наш сайт создан для тех, кто любит телевидение прошлого и интересуется телевидением настоящего. <br>
                    В нашем архиве вы можете найти записи старых телепередач <a href="/video/search?dates_range.end=479376360">времён раннего СССР</a>, <a href="/video/search?dates_range.start=479376360&dates_range.end=693738360">перестроечных лет</a>, <a href="/video/search?dates_range.start=693738360&dates_range.end=946631160">девяностых</a> и <a href="/video/search?dates_range.start=946631160">начала 2000-х</a> годов.<br>
                    Отдельное внимание уделено теме <a href="/video/graphics">телевизионного дизайна</a> и <a href="/video/commercials">рекламы</a>.
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
                <div class="box box--stretch">
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
            </div>
        </div>
        <div class="row">
            <div class="col">
                <div class="box">
                    <a href="/articles" class="box__heading">
                        <div class="box__heading__inner">
                           Статьи
                        </div>
                    </a>
                    <div class="box__inner box__inner--no-padding">
                        <div class="articles-list">
                            <div class="articles-list__block articles-list__block--big">
                                @foreach ($first_articles as $first_article)
                                    <a href="{{$first_article->url}}" style="background-image:url({{$first_article->cover_url}})" class="article article--big">
                                        <div class="article__texts">
                                            <div class="article__title">{{$first_article->title}}</div>
                                        </div>
                                    </a>
                               @endforeach
                            </div>
                            <div class="articles-list__block">
                                @foreach ($articles as $article)
                                    <a href="{{$article->url}}" style="background-image:url({{$article->cover_url}})" class="article">
                                        <div class="article__texts">
                                            <div class="article__title">{{$article->title}}</div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row row--stretch">
            <div class="col col--1-5">
                <div class="box">
                    <a href="/news" class="box__heading">
                        <div class="box__heading__inner">
                            Новости
                        </div>
                    </a>
                    <div class="box__inner box__inner--no-padding">
                        @foreach ($news as $news_item)
                            @include('blocks/article_small', ['article' => $news_item])
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="box box--stretch">
                    <a href="/forum" class="box__heading">
                        <div class="box__heading__inner">
                            Форум
                        </div>
                    </a>
                    <div class="box__inner box__inner--no-padding">
                        <div class="forum-topics-list">
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
            </div>
            <div class="col">
                @include('blocks/banner')
            </div>

        </div>
    </div>
@endsection
