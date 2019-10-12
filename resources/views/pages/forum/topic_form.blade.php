@extends('layouts.default')
@section('content')
    <div class="forum-page">
        <div class="forum-section">
            <div class="forum-section__breadcrumbs">
                <a class="forum-section__breadcrumb" href="/forum">Форум</a>
                @if ($parent_forum) <a class="forum-section__breadcrumb" href="/forum/{{$parent_forum->id}}">{{$parent_forum->title}}</a> @endif
                <a class="forum-section__breadcrumb" href="/forum/{{$forum->id}}">{{$forum->title}}</a>
                @if ($topic)
                <a class="forum-section__breadcrumb" href="/forum/{{$forum->id}}-{{$topic->id}}-1">{{$topic->title}}</a>
                @else
                <a class="forum-section__breadcrumb">Новая тема</a>
                @endif
            </div>
            <form class="form box" method="POST">
                <div class="box__heading">
                    {{$topic ? 'Редактировать тему "'.$topic->title.'"' : "Создать новую тему"}}
                </div>
                <div class="box__inner">
                    <div class="response"></div>
                    <div class="input-container">
                        <label class="input-container__label">Название</label>
                        <div class="input-container__inner">
                            <input class="input" name="title" value="{{$topic ? $topic->title : ""}}"/>
                            <span class="input-container__message"></span>
                        </div>
                    </div>
                    <div class="input-container">
                        <label class="input-container__label">Описание</label>
                        <div class="input-container__inner">
                            <input class="input" name="description" value="{{$topic ? $topic->name : ""}}"/>
                            <span class="input-container__message"></span>
                        </div>
                    </div>
                    @if (!$topic)
                    <div class="input-container">
                        <label class="input-container__label">Сообщение</label>
                        <div class="input-container__inner">
                            <div class="forum-section__form bb-editor">
                            @include('blocks/forum_form', ['show_buttons' => false])
                            </div>
                        </div>
                    </div>
                    @endif
                    <div class="input-container">
                        <label class="input-container__label">Опции темы</label>
                        <div class="input-container__inner">
                            <div class="input-container__checkboxes-row">
                                @if (\App\Helpers\PermissionsHelper::allows('frmesont'))
                                    <div class="input-container__checkboxes-row__col">
                                        <label class="input-container input-container--checkbox">
                                            <input type="checkbox" name="first_message_fixed" {{$topic && $topic->first_message_fixed ? "checked" : ""}}>
                                            <div class="input-container--checkbox__element"></div>
                                            <div class="input-container__label">Первое сообщение всегда сверху</div>
                                        </label>
                                    </div>
                                @endif
                                @if (\App\Helpers\PermissionsHelper::allows('frthont'))
                                     <div class="input-container__checkboxes-row__col">
                                        <label class="input-container input-container--checkbox">
                                            <input type="checkbox" name="is_fixed" {{$topic && $topic->is_fixed ? "checked" : ""}}>
                                            <div class="input-container--checkbox__element"></div>
                                            <div class="input-container__label">Зафиксировать тему</div>
                                        </label>
                                    </div>
                                @endif
                                @if (\App\Helpers\PermissionsHelper::allows('frclthr'))
                                    <div class="input-container__checkboxes-row__col">
                                        <label class="input-container input-container--checkbox">
                                            <input type="checkbox" name="is_closed" {{$topic && $topic->is_closed ? "checked" : ""}}>
                                            <div class="input-container--checkbox__element"></div>
                                            <div class="input-container__label">Закрыть тему</div>
                                        </label>
                                    </div>
                                @endif
                            </div>
                            <span class="input-container__message"></span>
                        </div>
                    </div>
                    <button class="button">Сохранить</button>
                </div>
                @csrf
            </form>
        </div>
    </div>
@endsection
@section ('scripts')
    <script>
        var bb = new bbCodes();
        bb.init('message');
    </script>
@endsection