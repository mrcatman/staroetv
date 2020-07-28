@extends('layouts.default')
@section('page-title')
    @if ($category){{$category->title}} - @endif {{$title}} @if ($search) [Поиск: {{$search}}] @endif
@endsection
@section('content')
 <div class="inner-page">
    <div class="inner-page__header">
        <div class="inner-page__header__title inner-page__header__title--with-buttons">
            {{$title}} @if ($search) (Поиск: "{{$search}}") @endif
            <div class="buttons-row">
                @if ($can_add)
                    <a href="{{$add_link}}" class="button">{{$add_title}}</a>
                @endif
                @if ($can_approve)
                    @if ($show_all)
                        <a href="{{$base_url}}" class="button">Показать только одобренные</a>
                    @else
                        <a href="{{$base_url}}?show_all=1" class="button">Показать все</a>
                    @endif
                @endif
            </div>
        </div>
       <div class="inner-page__header__right inner-page__header__right--big">

           <form action="{{$base_url}}" class="small-search-form">
               @csrf
               <input class="input" name="search" @if ($search) value="{{$search}}" @endif placeholder="Поиск">
               <button class="button button--light" type="submit">Найти</button>
           </form>

        </div>
    </div>
    <div class="inner-page__content">
        <div class="categories-list">
            <a class="category @if (!$category) category--active @endif" href="{{$base_url}}">Все категории</a>
            @foreach ($categories as $category_item)
                <a class="category @if ($category && $category_item->id == $category->id) category--active @endif" href="{{$category_item->full_url}}">{{$category_item->title}}</a>
            @endforeach
        </div>
        <div class="row">
            <div class="col">
                <div class="news-list">
                    @foreach ($articles as $index => $news_item)
                        @php ($first = $index == 0)
                        @php ($before_last = $index == count($articles) - 2)
                        @php ($last = $index == count($articles) - 1)
                        @php ($next_news_item = $articles[$index + 1])
                        @php ($next_news_item_2 = $articles[$index + 2])
                        @php ($next_news_item_3 = $articles[$index + 3])
                        @php ($before_fill = ($index == count($articles) - 3) ||  $next_news_item_3 && (!$next_news_item_3->cover_url || $next_news_item_3->cover_url == ""))
                        @php ($fill = ($index == count($articles) - 2) ||  $next_news_item_2 && (!$next_news_item_2->cover_url || $next_news_item_2->cover_url == ""))
                        @php ($full_width = !$next_news_item || (!$next_news_item->cover_url || $next_news_item->cover_url == ""))
                        @include('blocks/article', ['article' => $news_item, 'full_width' => $full_width, 'fill' => $fill, 'before_fill' => $before_fill])
                    @endforeach
                </div>
                <div class="pager-container pager-container--box">
                    {{$articles->appends(request()->except('_token'))->links()}}
                </div>
            </div>
        </div>
    </div>
 </div>
@endsection
