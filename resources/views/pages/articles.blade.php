@extends('layouts.default')
@section('page-title')
    @if ($category){{$category->title}} - @endif {{$title}}
@endsection
@section('content')
 <div class="inner-page">
    <div class="inner-page__header">
        <div class="inner-page__header__title">
            {{$title}}
        </div>
        <div class="inner-page__header__right inner-page__header__right--big">
            @if ($can_add)
            <a href="{{$add_link}}" class="button">{{$add_title}}</a>
            @endif
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
                @foreach ($articles as $news_item)
                    @include('blocks/article', ['article' => $news_item])
                @endforeach
                <div class="pager-container pager-container--light">
                    {{$articles->links()}}
                </div>
              
            </div>
        </div>
    </div>
 </div>
@endsection
