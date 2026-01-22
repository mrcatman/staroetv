@extends('layouts.default', ['vue' => true])
@section('content')
    <form class="form box" method="POST">
        <div class="box__heading">
            <div class="box__heading__inner">
                {{ $page ? "Редактировать страницу: ".$page->title : "Добавить страницу" }}
            </div>
            @if ($page)
                <div class="box__heading__buttons">
                    <a href="{{$page->full_url}}" class="button button--light">Назад</a>
                </div>
            @endif
        </div>
        <div class="box__inner">
            <div class="form__content">
                <div class="response"></div>

                <div class="input-container">
                    <label class="input-container__label">Заголовок<span
                            class="input-container__required">*</span></label>
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
                        <tiptap-editor name="content" :content='@json($page ? $page->content : '')'></tiptap-editor>
                        <span class="input-container__message"></span>
                    </div>
                </div>
                <div class="input-container">
                    <label class="input-container__label">Смотреть страницу могут:</label>
                    <div class="input-container__inner">
                        @include('blocks.forms.user-groups-select', ['name' => 'can_read', 'data' => $page ? $page->can_read : "0"])
                        <span class="input-container__message"></span>
                    </div>
                </div>
                <button class="button">Сохранить</button>
            </div>
        </div>
        @csrf
    </form>
@endsection
