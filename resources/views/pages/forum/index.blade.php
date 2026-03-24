@extends('layouts.default')
@section('page-title')
    Форум
@endsection
@section('content')
    <div class="col forum-page">
        <div class="forum-section">
            <div class="forum__top-panel__outer">
                <div class="forum__top-panel">
                    <div class="forum__top-panel__inner">
                        @include('blocks.forum.buttons')
                        <div
                            class="forum-section__breadcrumbs forum-section__breadcrumbs--index forum-section__breadcrumbs--with-search">
                            <a class="forum-section__breadcrumb" href="{{route('forum.index')}}">Форум</a>
                            <div class="forum-section__title__buttons">
                                @if (\App\Helpers\PermissionsHelper::allows('fredit'))
                                    <a class="button" href="{{route('forum.subforums.new', 0)}}">Новый форум</a>
                                @endif
                            </div>
                            @include('blocks.forum.search')
                        </div>
                    </div>
                </div>

            </div>
        </div>
        @foreach ($forums as $forum)
            @if (count($forum->subforums) > 0)
            <div class="box">
                <div class="box__heading">
                    <div class="box__heading__inner">{{$forum->title}}</div>
                </div>
                <div class="box__inner">
                    <div class="forum__list">
                        @foreach ($forum->subforums as $subforum)
                            @include('blocks.forum.subforum', ['subforum' => $subforum])
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        @endforeach

        <div class="row row--align-start">
            <div class="col">
                <div class="box">
                    <div class="box__heading">
                        <div class="box__heading__inner">Сейчас на форуме</div>
                    </div>
                    <div class="box__inner">
                        @foreach ($users_on_forum as $user)
                            <a target="_blank" href="{{$user->url}}" class="user-online"
                               data-group-id="{{$user->group_id}}">{{$user->username}}</a>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="box">
                    <div class="box__heading">
                        <div class="box__heading__inner">Статистика</div>
                    </div>
                    <div class="box__inner">
                        <div class="forum__stats">
                            Всего создано <strong>{{$stats['topics_count']}}</strong> тем, в которые добавлено
                            <strong>{{$stats['messages_count']}}</strong> ответов.<br>
                            Зарегистрировано <strong>{{$stats['users_count']}}</strong> участников. Приветствуем нового
                            участника
                            <strong>
                                <a target="_blank"
                                   href="{{$stats['last_user']->url}}">{{$stats['last_user']->username}}</a>
                            </strong>.

                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
