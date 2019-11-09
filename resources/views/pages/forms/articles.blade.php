@extends('layouts.default')
@section('page-title')
 {{$title}}
@endsection
@section('content')
    <form class="form box" action="{{$article ? "/articles/edit/".$article->id : "/articles/add"}}" method="POST">
        <div class="box__heading">
            {{ $title }}
            <div class="box__heading__right">
                @if ($article)
                    <a href="{{$article->full_url}}" class="box__heading__link">Назад</a>
                @endif
            </div>
        </div>
        <div class="box__inner">
            <div class="response"></div>
            <input type="hidden" name="type_id" value="{{$type_id}}"/>
            <div class="input-container">
                <label class="input-container__label">Заголовок<span class="input-container__required">*</span></label>
                <div class="input-container__inner">
                    <input class="input" name="title" value="{{$article ? $article->title : ""}}"/>
                    <span class="input-container__message"></span>
                </div>
            </div>
            @if ($type_id === \App\Article::TYPE_NEWS || $type_id === \App\Article::TYPE_ARTICLES)
                <div class="input-container">
                    <label class="input-container__label">Краткое описание</label>
                    <div class="input-container__inner">
                        <div class="input-container__element-outer">
                            <textarea class="input" name="short_content">{{$article ? $article->short_content : ""}}</textarea>
                            <span class="input-container__message"></span>
                        </div>
                    </div>
                </div>
            @endif
            <div class="input-container">
                <label class="input-container__label">Текст<span class="input-container__required">*</span></label>
                <div class="input-container__inner">
                    <textarea id="editor" class="input input--textarea" name="content">{{$article ? $article->content : ""}}</textarea>
                    <span class="input-container__message"></span>
                </div>
            </div>
            @if ($type_id === \App\Article::TYPE_NEWS || $type_id === \App\Article::TYPE_ARTICLES)
                <div class="input-container">
                    <label class="input-container__label">Источник</label>
                    <div class="input-container__inner">
                        <input class="input" name="source" value="{{$article ? $article->source : ""}}"/>
                        <span class="input-container__message"></span>
                    </div>
                </div>
            @endif
            <div class="input-container">
                <label class="input-container__label">Обложка</label>
                <div class="input-container__inner">
                    <picture-uploader name="cover_id" :data="{{$article && $article->cover_picture ? $article->cover_picture : "null"}}" />
                    <span class="input-container__message"></span>
                </div>
            </div>
            <div class="input-container">
                <label class="input-container__label">Категория</label>
                <div class="input-container__inner">
                    <div class="radio-buttons radio-buttons--inline">
                        @foreach ($categories as $category)
                        <label class="radio-button">
                            <input type="radio" name="category_id" @if ($article && $article->category_id == $category->original_id) checked="checked" @endif value="{{$category->original_id}}"/>
                            <div class="radio-button__circle"></div>
                            <div class="radio-button__text">{{$category->title}}</div>
                        </label>
                        @endforeach
                    </div>
                    <span class="input-container__message"></span>
                </div>
            </div>
            <crossposts-editor :crossposts="{{$crossposts}}" :article="{{$article}}" :networks="{{$networks}}"/>
            <button class="button">Сохранить</button>
        </div>
        @csrf
    </form>
@endsection
