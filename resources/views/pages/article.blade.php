@extends('layouts.default')
@section('head')
    <meta property="og:title" content="{{$article->title}}" />
    <meta property="og:description" content="{{$article->short_content}}" />
    <meta property='og:type' content="article" />
    @if ($article->cover != "")
    <meta property="og:image" content="{{$article->cover}}" />
    @endif
@endsection
@section('page-title')
    {{$article->title}}
@endsection
@section('content')
    <div class="inner-page inner-page--article">
        @if ($article->cover != "")
            <div class="inner-page__cover-block" style="background-image:url({{$article->cover_url}})">
                <div class="inner-page__cover-block__panel">
                    <span data-id="{{$article->id}}" class="button button--dropdown button--small button--monochrome button--article-menu">
                        <span class="button--dropdown__text">Действия</span>
                         <span class="button--dropdown__icon">
                             <i class="fa fa-chevron-down"></i>
                         </span>
                         <div class="button--dropdown__list" id="actions_list_{{$article->id}}"></div>
                     </span>
                </div>
                <div class="inner-page__cover-block__texts">
                    <div class="inner-page__cover-block__title">{{$article->title}}</div>
                    <div class="inner-page__cover-block__info">
                        <div class="icon-blocks">
                            <span class="icon-block"><i class="fa fa-calendar"></i><span class="icon-block__text">{{$article->created_at}}</span></span>
                            <span class="icon-block"><i class="fa fa-eye"></i><span class="icon-block__text">{{$article->views}}</span></span>
                            @if ($article->user)
                                <a href="{{$article->user->url}}" class="icon-block"><i class="fa fa-user"></i><span class="icon-block__text">{{$article->user->username}}</span></a>
                            @else
                                <span class="icon-block"><i class="fa fa-user"></i><span class="icon-block__text">{{$article->username}}</span></span>
                            @endif
                            @if ($article->source != '')
                                <a target=_blank href="{{$article->source}}" class="icon-block"><i class="fa fa-link"></i><span class="icon-block__text">{{$article->source}}</span></a>
                            @endif
                            @include('blocks/share')

                        </div>
                    </div>
                </div>
            </div>
        @else
        <div class="inner-page__header">
            <div class="inner-page__header__title">{{$article->title}}</div>
            <div class="inner-page__header__right">
                <span data-id="{{$article->id}}" class="button button--dropdown button--small button--monochrome button--article-menu">
                    <span class="button--dropdown__text">Действия</span>
                    <span class="button--dropdown__icon">
                        <i class="fa fa-chevron-down"></i>
                    </span>
                    <div class="button--dropdown__list" id="actions_list_{{$article->id}}"></div>
                </span>
            </div>
        </div>
        @endif
        <div class="row row--stretch">
            <div class="col col--2">
                <div class="row row--vertical">
                    <div class="box">
                        <div class="box__inner">
                            <div class="inner-page__content">
                                <div class="inner-page__text">
                                    {!! $article->fixed_content !!}
                                </div>
                                <div class="inner-page--article__bottom">
                                    <div class="icon-blocks">
                                        <span class="icon-block"><i class="fa fa-calendar"></i><span class="icon-block__text">{{$article->created_at}}</span></span>
                                        <span class="icon-block"><i class="fa fa-eye"></i><span class="icon-block__text">{{$article->views}}</span></span>
                                        @if ($article->user)
                                            <a href="{{$article->user->url}}" class="icon-block"><i class="fa fa-user"></i><span class="icon-block__text">{{$article->user->username}}</span></a>
                                        @else
                                            <span class="icon-block"><i class="fa fa-user"></i><span class="icon-block__text">{{$article->username}}</span></span>
                                        @endif
                                        @if ($article->source != '')
                                        <a target=_blank href="{{$article->source}}" class="icon-block"><i class="fa fa-link"></i><span class="icon-block__text">{{$article->source}}</span></a>
                                        @endif
                                         @include('blocks/share')

                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    @include('blocks/comments', ['ajax' => false, 'lazyload' => true, 'page' => 1, 'conditions' => ['material_type' => $article->type_id, 'material_id' => $article->original_id]])
                </div>
            </div>
            <div class="col">
                <div class="box box--see-also">
                    <div class="box__heading">
                        Поиск по разделу
                    </div>
                    <div class="box__inner">
                        <form action="{{$search_url}}" class="small-search-form">
                            @csrf
                            <input class="input" name="search" placeholder="Поиск">
                            <button class="button" type="submit">Найти</button>
                        </form>
                    </div>
                    <div class="box__heading">
                        Смотрите также
                    </div>
                    <div class="box__inner">
                        <div class="see-also">
                            @foreach ($see_also as $see_also_item)
                                @include('blocks/article_small', ['article' => $see_also_item])
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
