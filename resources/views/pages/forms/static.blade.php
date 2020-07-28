@extends('layouts.default')
@section('content')
    <form class="form box" method="POST">
        <div class="box__heading">
            {{ $page ? "Редактировать страницу: ".$page->title : "Добавить страницу" }}
            <div class="box__heading__right">
                @if ($page)
                    <a href="{{$page->full_url}}" class="box__heading__link">Назад</a>
                @endif
            </div>
        </div>
        <div class="box__inner">
            <div class="response"></div>
            <div class="input-container">
                <label class="input-container__label">Заголовок<span class="input-container__required">*</span></label>
                <div class="input-container__inner">
                    <input class="input" name="title" value="{{$page ? $page->title : ""}}"/>
                    <span class="input-container__message"></span>
                </div>
            </div>
            <div class="input-container">
                <label class="input-container__label">Короткий URL</label>
                <div class="input-container__inner">
                    <input class="input" name="url" value="{{$page ? $page->url : ""}}"/>
                    <span class="input-container__message"></span>
                </div>
            </div>
            <div class="input-container">
                <label class="input-container__label">Текст<span class="input-container__required">*</span></label>
                <div class="input-container__inner">
                    <textarea id="editor" class="input input--textarea" name="content">{{$page ? $page->content : ""}}</textarea>
                    <span class="input-container__message"></span>
                </div>
            </div>
            <div class="input-container">
                <label class="input-container__label">Смотреть страницу могут:</label>
                <div class="input-container__inner">
                    @include('blocks/user_groups_select', ['name' => 'can_read', 'data' => $page ? $page->can_read : "0"])
                    <span class="input-container__message"></span>
                </div>
            </div>
            <button class="button">Сохранить</button>
        </div>
        @csrf
    </form>
@endsection
