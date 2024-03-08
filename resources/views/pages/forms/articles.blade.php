@extends('layouts.default')
@section('page-title')
 {{$article ? "Редактировать публикацию" : "Добавить публикацию"}}
@endsection
@section('content')
    <form class="form box" action="{{$article ? "/articles/edit/".$article->id : "/articles/add"}}" method="POST">
        <div class="box__heading">
            {{$article ? "Редактировать публикацию" : "Добавить публикацию"}}
            <div class="box__heading__right">
                @if ($article)
                    <a href="{{$article->full_url}}" class="box__heading__link">Назад</a>
                @endif
            </div>
        </div>
        <div class="box__inner">
            <div class="response"></div>
            <div class="input-container">
                <label class="input-container__label">Заголовок<span class="input-container__required">*</span></label>
                <div class="input-container__inner">
                    <input class="input" name="title" value="{{$article ? $article->title : ""}}"/>
                    <span class="input-container__message"></span>
                </div>
            </div>
            <div class="input-container">
                <label class="input-container__label">Краткое описание</label>
                <div class="input-container__inner">
                    <div class="input-container__element-outer">
                        <textarea class="input" name="short_content">{{$article ? $article->short_content : ""}}</textarea>
                        <span class="input-container__message"></span>
                    </div>
                </div>
            </div>
            <div class="input-container">
                <label class="input-container__label">Текст<span class="input-container__required">*</span></label>
                <div class="input-container__inner">
                    <textarea id="editor" class="input input--textarea" name="content">{{$article ? $article->content : ""}}</textarea>
                    <span class="input-container__message"></span>
                </div>
            </div>
            <div class="input-container">
                <label class="input-container__label">Короткий URL</label>
                <div class="input-container__inner">
                    <input class="input" name="slug" value="{{$article ? $article->slug : ""}}"/>
                    <span class="input-container__message"></span>
                </div>
            </div>
            <div class="input-container">
                <label class="input-container__label">Источник</label>
                <div class="input-container__inner">
                    <input class="input" name="source" value="{{$article ? $article->source : ""}}"/>
                    <span class="input-container__message"></span>
                </div>
            </div>
            <div class="input-container">
                <label class="input-container__label">Обложка</label>
                <div class="input-container__inner">
                    <picture-uploader name="cover_id" :data="{{$article && $article->coverPicture ? $article->coverPicture : "null"}}" />
                    <span class="input-container__message"></span>
                </div>
            </div>
            <div class="input-container">
                <label class="input-container__label">Теги</label>
                <div class="input-container__inner">
                    <tags-editor :tags="{{$article ? $article->tags : '[]'}}" :all-tags="{{\App\Tag::all()}}"/>
                    <span class="input-container__message"></span>
                </div>
            </div>
            <div class="input-container">
                <label class="input-container__label">Привязка</label>
                <div class="input-container__inner">
                    <article-bindings-editor :bindings="{{$article ? $article->bindings : '[]'}}" />
                    <span class="input-container__message"></span>
                </div>
            </div>

            <div>
                @if (isset($networks) && $networks)
                    <crossposts-editor :crossposts="{{$crossposts}}" :article="{{$article}}" :networks="{{$networks}}"/>
                @endif
            </div>
            <button class="button">Сохранить</button>

        </div>
        @csrf
    </form>
@endsection
