@extends('layouts.default')
@section('content')
    <div class="forum-page">
        <div class="forum-section">
            <div class="forum-section__breadcrumbs">
                <a class="forum-section__breadcrumb" href="/forum">Форум</a>
                @if ($parent)
                    <a class="forum-section__breadcrumb" href="/forum/{{$parent->id}}">{{$parent->title}}</a>
                @endif
                @if ($forum)
                <a class="forum-section__breadcrumb" href="/forum/{{$forum->id}}">{{$forum->title}}</a>
                @else
                <a class="forum-section__breadcrumb">Новый форум</a>
                @endif
            </div>
            <form class="form box" action="{{$forum ? "/forum/edit/".$forum->id : "/forum/new"}}" method="POST">
                <div class="box__heading">
                    {{$forum ? 'Редактировать форум "'.$forum->title.'"' : "Создать новый форум"}}
                </div>
                <div class="box__inner">
                    <div class="response"></div>
                    <div class="input-container">
                        <label class="input-container__label">Название</label>
                        <div class="input-container__inner">
                            <input class="input" name="title" value="{{$forum ? $forum->title : ""}}"/>
                            <span class="input-container__message"></span>
                        </div>
                    </div>
                    <div class="input-container">
                        <label class="input-container__label">Описание</label>
                        <div class="input-container__inner">
                            <textarea class="input" name="description">{{$forum ? $forum->description : ""}}</textarea>
                            <span class="input-container__message"></span>
                        </div>
                    </div>
                    @if ($is_section && $forum)
                        <div class="input-container">
                            <label class="input-container__label">Удалить и переместить подфорумы:</label>
                            <div class="input-container__inner">
                                <select class="select-classic" name="move_subforums_to">
                                    <option value="-1">Не удалять</option>
                                    @foreach ($sections as $section)
                                        <option value="{{$section->id}}" @if ($parent_id == $section->id) selected @endif>{{$section->title}}</option>
                                    @endforeach
                                </select>
                                <span class="input-container__message"></span>
                            </div>
                        </div>
                    @endif
                    @if (!$is_section)
                    <div class="input-container">
                        <label class="input-container__label">Раздел</label>
                        <div class="input-container__inner">
                            <select class="select-classic" name="parent_id">
                                @foreach ($sections as $section)
                                <option value="{{$section->id}}" @if ($parent_id == $section->id) selected @endif>{{$section->title}}</option>
                                @endforeach
                            </select>
                            <span class="input-container__message"></span>
                        </div>
                    </div>
                    <div class="input-container">
                        <label class="input-container__label">Состояние</label>
                        <div class="input-container__inner">
                            <select class="select-classic" name="state" id="forum_state">
                                <option value="1" @if ($forum && $forum->state == 1) selected @endif>Активен</option>
                                <option value="2" @if ($forum && $forum->state == 2) selected @endif>Закрыт (архив)</option>
                                <option value="3" @if ($forum && $forum->state == 3) selected @endif>Отключен</option>
                                @if ($forum)
                                <option value="4">Удалить</option>
                                @endif
                            </select>
                            <span class="input-container__message"></span>
                        </div>
                    </div>
                    <div class="input-container" id="forum_move_to" style="display:none">
                        <label class="input-container__label">Переместить темы в:</label>
                        <div class="input-container__inner">
                            <select class="select-classic" name="move_to">
                                @foreach ($forums as $forum_item)
                                    <option value="{{$forum_item->id}}">{{$forum_item->title}}</option>
                                @endforeach
                            </select>
                            <span class="input-container__message"></span>
                        </div>
                    </div>
                    <div class="input-container">
                        <label class="input-container__label">Читать записи в форуме могут:</label>
                        <div class="input-container__inner">
                            @include('blocks/user_groups_select', ['name' => 'can_view', 'data' => $forum ? $forum->can_view : "0"])
                            <span class="input-container__message"></span>
                        </div>
                    </div>
                    <div class="input-container">
                        <label class="input-container__label">Создавать новые темы могут:</label>
                        <div class="input-container__inner">
                            @include('blocks/user_groups_select', ['name' => 'can_create_topics', 'data' => $forum ? $forum->can_create_topics : "0"])
                            <span class="input-container__message"></span>
                        </div>
                    </div>
                    <div class="input-container">
                        <label class="input-container__label">Добавлять ответы могут:</label>
                        <div class="input-container__inner">
                            @include('blocks/user_groups_select', ['name' => 'can_post', 'data' => $forum ? $forum->can_post : "0"])
                            <span class="input-container__message"></span>
                        </div>
                    </div>
                    @endif
                    <button class="button">Сохранить</button>
                </div>
                @csrf
            </form>
        </div>
    </div>
@endsection