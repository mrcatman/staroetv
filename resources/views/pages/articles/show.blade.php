@extends('layouts.default')
@section('head')
    <meta property="og:title" content="{{$article->title}}"/>
    <meta property="og:description" content="{{$article->short_content}}"/>
    <meta property='og:type' content="article"/>
    @if ($article->cover_url != "")
        <meta property="og:image" content="{{asset($article->cover_url)}}"/>
    @endif
@endsection
@section('page-title')
    {{$article->title}}
@endsection
@section('content')

    <div class="row row--stretch">
        <div class="col col--2">
            <div class="box">
                <div class="box__inner">
                    <div class="article-page">
                        <h1 class="article-page__title">
                            {{$article->title}}
                        </h1>
                        @if ($article->cover_url != "")
                            <img class="article-page__picture" src="{{$article->cover_url}}">
                        @endif
                        <div class="icon-blocks">
                            <span class="icon-block"><i class="fa fa-calendar"></i><span
                                    class="icon-block__text">{{$article->created_at}}</span></span>
                            <span class="icon-block"><i class="fa fa-eye"></i><span
                                    class="icon-block__text">{{$article->views}}</span></span>
                            @if ($article->user)
                                <a href="{{$article->user->url}}" class="icon-block"><i class="fa fa-user"></i><span
                                        class="icon-block__text">{{$article->user->username}}</span></a>
                            @else
                                <span class="icon-block"><i class="fa fa-user"></i><span
                                        class="icon-block__text">{{$article->username}}</span></span>
                            @endif
                            @if ($article->source != '' && !str_contains($article->source, 'staroetv.su'))
                                <span class="icon-block">
                                    <i class="fa fa-link"></i>
                                    <span class="icon-block__text">{!! $article->source_with_links !!}</span>
                                </span>
                            @endif
                            @if ($show_actions_panel)
                            <span data-id="{{$article->id}}" class="button button--dropdown button--small button--light button--article-menu">
                                <span class="button--dropdown__text">Действия</span>
                                <span class="button--dropdown__icon">
                                    <i class="fa fa-chevron-down"></i>
                                </span>
                                <div class="menu button--dropdown__list">
                                @if ($can_edit)
                                    <a class="menu__item button--dropdown__list__item" href="{{route('articles.edit', $article->id)}}">Редактировать</a>
                                    <a class="menu__item button-dropdown__list__item button--delete-article">Удалить</a>
                                @endif
                                @if ($can_approve)
                                    <a class="menu__item button--dropdown__list__item" data-approve="articles" data-approve-id="{{$article->id}}">{{$article->pending ? "Одобрить" : "Скрыть"}}</a>
                                @endif
                                </div>
                            </span>
                            @endif
                        </div>

                        <div class="tags-list">
                            @foreach ($article->tags as $tag)
                                <a href="{{route('articles.index', ['tag' => $tag->url])}}"
                                   class="tags-list__item">{{$tag->name}}</a>
                            @endforeach
                        </div>

                        <div class="text-content">
                            {!! $article->fixed_content !!}
                        </div>
                    </div>


                </div>
            </div>
            @include('blocks.comments.list', ['ajax' => false, 'lazyload' => true, 'page' => 1, 'conditions' => ['material_type' => $article->type_id ? $article->type_id : 1, 'material_id' => $article->original_id]])

        </div>
        <div class="col col--sidebar">
            <div class="box">
                <div class="box__heading">
                    <div class="box__heading__inner">
                        Поиск по разделу
                    </div>
                </div>
                <div class="box__inner">
                    <form action="{{route('articles.index')}}" class="small-search-form">
                        @csrf
                        <div class="input-container__inner input-container__inner--with-icon">
                            <i class="fa fa-search input-container__icon"></i>
                            <input class="input" name="search" placeholder="Поиск">
                        </div>
                        <button class="button button--light" type="submit">Найти</button>
                    </form>
                </div>
            </div>
            <div class="box">
                <div class="box__heading">
                    <div class="box__heading__inner">
                        Читайте также
                    </div>
                </div>
                <div class="box__inner">
                    <div class="see-also">
                        @foreach ($see_also as $see_also_item)
                            @include('blocks.articles.item-small', ['article' => $see_also_item])
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

@if ($can_edit)
    <div id="delete_article" data-title="Удалить статью" style="display:none">
        <form action="{{route('articles.delete')}}" class="form modal-window__form" data-auto-close-modal="1">
            <input type="hidden" name="id" value="{{$article->id}}"/>
            <div class="modal-window__text">
                Вы уверены, что хотите удалить статью?
            </div>
            <div class="form__bottom">
                <button class="button button--light">ОК</button>
                <a class="button button--light modal-window__close-button">Отмена</a>
                <div class="response response--light"></div>
            </div>
        </form>
    </div>
@endif

@endsection
