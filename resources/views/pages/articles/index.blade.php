@extends('layouts.default')
@section('page-title')
    @if ($tag){{$tag->name}} - @endif Публикации @if ($search) [Поиск: {{$search}}] @endif
@endsection
@section('content')
<div class="box">
    <div class="box__heading">
        <div class="box__heading__inner">
            Публикации @if ($tag) с тегом "{{$tag->name}}" @endif @if ($search) (Поиск: "{{$search}}") @endif
        </div>
        <div class="box__heading__buttons">
            <div class="buttons-row">
            @if ($can_add)
                <a href="/articles/add" class="button">
                    <i class="fa fa-plus"></i>
                    Добавить
                </a>
            @endif
            @if ($can_approve)
                @if ($show_all)
                    <a href="/articles" class="button">
                        <i class="fa fa-list"></i>
                        Показать только одобренные
                    </a>
                @else
                    <a href="/articles?show_all=1" class="button">
                        <i class="fa fa-list"></i>Показать все
                    </a>
                @endif
            @endif
            </div>
        </div>
        <div class="box__heading__right">
            <form action="/articles" class="input-container small-search-form">
                @csrf
                <div class="input-container__inner input-container__inner--with-icon">
                    <i class="fa fa-search input-container__icon"></i>
                    <input class="input" name="search" @if ($search) value="{{$search}}" @endif placeholder="Поиск">
                </div>
                <button class="button button--light" type="submit">Найти</button>
            </form>
        </div>
    </div>
    <div class="box__inner">

        <div class="categories-list categories-list--multiline">
            <a class="category @if (!$tag) category--active @endif" href="/articles">Все теги</a>
            @foreach ($tags as $tag_item)
                <a class="category @if ($tag && $tag_item->id == $tag->id) category--active @endif" href="/articles?tag={{$tag_item->url}}">
                    {{$tag_item->name}}
                    <span class="category__count">{{$tag_item->count}}</span>
                </a>
            @endforeach
        </div>
        <div class="row">
            <div class="col">
                <div class="news-blocks-list">
                    @foreach ($articles as $news_item)
                        @include('blocks/news', ['class' => 'news-block--card', 'show_cover' => true, 'news_item' => $news_item])
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    <div class="box__pager">
        {{$articles->appends(request()->except('_token'))->links()}}
    </div>
 </div>
@endsection
