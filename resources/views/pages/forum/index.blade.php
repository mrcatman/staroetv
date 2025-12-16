@extends('layouts.default')
@section('page-title')
    Форум
@endsection
@section('content')
    <div class="forum-page">
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
                            <form action="{{route('forum.index')}}" method="GET"
                                  class="forum-section__search forum-section__search--subforum">
                                <input placeholder="Поиск по форуму" class="input" name="s" value="{{$search}}">
                                <select class="select-classic" name="type">
                                    <option value="topics"
                                            @if (!isset($messages_view) || !$messages_view) selected @endif>Темы
                                    </option>
                                    <option value="messages"
                                            @if (isset($messages_view) && $messages_view) selected @endif>Сообщения
                                    </option>
                                </select>
                                <button type="submit" class="button"><i class="fa fa-search"></i>Искать</button>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        @foreach ($forums as $forum)
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
