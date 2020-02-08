@extends('layouts.default')
@section('content')
    <div class="forum-page">
        <div class="forum-section">
            <div class="forum-section__breadcrumbs forum-section__breadcrumbs--index forum-section__breadcrumbs--with-search">
                <a class="forum-section__breadcrumb" href="/forum">Форум</a>
                <div class="forum-section__title__buttons">
                    @if (\App\Helpers\PermissionsHelper::allows('fredit'))
                        <a class="button" href="/forum/0/new">Новый форум</a>
                    @endif
                </div>
                <form action="/forum/" method="GET" class="forum-section__search forum-section__search--subforum">
                    <input placeholder="Поиск по форуму" class="input" name="s" value="{{$search}}">
                    <select class="select-classic" name="type">
                        <option value="topics" @if (!isset($messages_view) || !$messages_view) selected @endif>Темы</option>
                        <option value="messages" @if (isset($messages_view) && $messages_view) selected @endif>Сообщения</option>
                    </select>
                    <button type="submit" class="button button--light">ОК</button>
                </form>
            </div>
        </div>
        @foreach ($forums as $forum)
        <div class="forum-section">
            <div class="forum-section__title">{{$forum->title}}</div>
            <div class="forum-section__children">
                @foreach ($forum->subforums as $subforum)
                    @include('blocks/subforum', ['$subforum' => $subforum])
                @endforeach
            </div>
        </div>
        @endforeach
        <div class="forum__bottom">
            <div class="forum__bottom__line">
                <div class="forum__bottom__line__heading">Сейчас на форуме</div>
                <div class="forum__bottom__line__text">
                    @foreach ($users_on_forum as $user)
                        <a target="_blank" href="{{$user->url}}" class="user-online" data-group-id="{{$user->group_id}}">{{$user->username}}</a>
                    @endforeach
                </div>
            </div>
            <div class="forum__bottom__line">
                <div class="forum__bottom__line__heading">Статистика</div>
                <div class="forum__bottom__line__text">
                    Всего создано <strong>{{$stats['topics_count']}}</strong> тем, в которые добавлено <strong>{{$stats['messages_count']}}</strong> ответов.<br>
                    Зарегистрировано <strong>{{$stats['users_count']}}</strong> участников. Приветствуем нового участника <strong><a target="_blank" href="{{$stats['last_user']->url}}">{{$stats['last_user']->username}}</a></strong>.
                </div>
            </div>
        </div>
    </div>
@endsection
