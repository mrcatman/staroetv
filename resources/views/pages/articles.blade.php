@extends('layouts.default')
@section('page-title')
    @if ($tag){{$tag->name}} - @endif Публикации @if ($search) [Поиск: {{$search}}] @endif
@endsection
@section('content')
 <div class="inner-page">
    <div class="inner-page__header">
        <div class="inner-page__header__title inner-page__header__title--with-buttons">
            Публикации @if ($tag) с тегом "{{$tag->name}}" @endif @if ($search) (Поиск: "{{$search}}") @endif
            <div class="buttons-row">
                @if ($can_add)
                    <a href="/articles/add" class="button">Добавить</a>
                @endif
                @if ($can_approve)
                    @if ($show_all)
                        <a href="/articles" class="button">Показать только одобренные</a>
                    @else
                        <a href="/articles?show_all=1" class="button">Показать все</a>
                    @endif
                @endif
            </div>
        </div>
       <div class="inner-page__header__right inner-page__header__right--big">

           <form action="/articles" class="small-search-form">
               @csrf
               <input class="input" name="search" @if ($search) value="{{$search}}" @endif placeholder="Поиск">
               <button class="button button--light" type="submit">Найти</button>
           </form>

        </div>
    </div>
    <div class="inner-page__content">
        <div class="categories-list categories-list--multiline articles-page__categories-list">
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
                <div class="pager-container news-blocks-list__pager-container">
                    {{$articles->appends(request()->except('_token'))->links()}}
                </div>
            </div>
        </div>
    </div>
 </div>
@endsection
