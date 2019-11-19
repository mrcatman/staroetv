@extends('layouts.default')
@section('head')
    <meta property="og:title" content="{{$article->title}}" />
    <meta property="og:description" content="{{$article->short_content}}" />
    <meta property='og:type' content="article" />
    @if ($article->cover != "")
    <meta property="og:image" content="{{$article->cover}}" />
    @endif
@endsection
@section('content')
    <div class="inner-page inner-page--article">
        @if ($article->cover != "")
            <div class="inner-page__cover-block" style="background-image:url({{$article->cover}})">
                <div class="inner-page__cover-block__panel">
                    @if ($can_edit || $can_delete)
                    <div class="inner-page__cover-block__panel__bg">
                        @if ($can_edit)
                            <a class="button button--light" href="{{$edit_link}}">{{$edit_title}}</a>
                        @endif
                        @if ($can_delete)
                            <a class="button button--light button--delete-article">{{$delete_title}}</a>
                        @endif
                        @if ($can_approve)
                            <a class="button button--light button--approve-article">{{$approve_title}}</a>
                        @endif
                    </div>
                    @endif
                </div>
                <div class="inner-page__cover-block__texts">
                    <div class="inner-page__cover-block__title">{{$article->title}}</div>
                    <div class="inner-page__cover-block__info">
                        <span class="inner-page__cover-block__date"><i class="fa fa-calendar"></i>{{$article->created_at}}</span>
                        <span class="inner-page__cover-block__views"><i class="fa fa-eye"></i>{{$article->views}}</span>
                        <span class="inner-page__cover-block__comments"><i class="fa fa-comment"></i>{{count($article->comments)}}</span>
                        @if ($article->user)
                            <a href="{{$article->user->url}}" class="inner-page__cover-block__user"><i class="fa fa-user"></i>{{$article->user->username}}</a>
                        @else
                            <span class="inner-page__cover-block__user"><i class="fa fa-user"></i>{{$article->username}}</span>
                        @endif
                        <a target=_blank href="{{$article->source}}" class="inner-page__cover-block__link"><i class="fa fa-link"></i>{{$article->source}}</a>
                    </div>
                </div>
            </div>
        @else
        <div class="inner-page__header">
            <div class="inner-page__header__title">{{$article->title}}</div>
            <div class="inner-page__header__right">
                @if ($can_edit || $can_delete)
                    @if ($can_edit)
                        <a class="button button--light" href="{{$edit_link}}">{{$edit_title}}</a>
                    @endif
                    @if ($can_delete)
                        <a class="button button--light button--delete-article">{{$delete_title}}</a>
                    @endif
                    @if ($can_approve)
                        <a class="button button--light button--approve-article">{{$approve_title}}</a>
                    @endif
                @endif
            </div>
        </div>
        @endif
        <div class="row row--stretch">
            <div class="col col--2">
                <div class="row row--vertical">
                    <div class="inner-page__content">
                        <div class="inner-page__icon-blocks-container">

                        </div>
                        <div class="inner-page__text">
                            {!! $article->content !!}
                        </div>
                    </div>
                    <div class="box box--comments">
                        <div class="box__heading">
                            <div class="box__heading__inner">
                                Комментарии <span class="box__heading__count">{{\App\Comment::where(['material_type' => $article->type_id, 'material_id' => $article->original_id])->count()}}</span>
                            </div>
                        </div>
                        <div class="box__inner">
                            @include('blocks/comments', ['ajax' => false, 'page' => 1, 'conditions' => ['material_type' => $article->type_id, 'material_id' => $article->original_id]])
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="box">
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
    @if ($can_delete)
        <div id="delete_article" data-title="Удалить запись" style="display:none">
            <form action="/articles/delete" class="form modal-window__form" data-auto-close-modal="1">
                <input type="hidden" name="article_id" value="{{$article->id}}"/>
                <div class="modal-window__small-text">
                    Вы уверены, что хотите удалить запись?
                </div>
                <div class="form__bottom">
                    <button class="button button--light">ОК</button>
                    <a class="button button--light modal-window__close-button">Отмена</a>
                    <div class="response response--light"></div>
                </div>
            </form>
        </div>
    @endif
    @if ($can_approve)
        <div id="approve_article" data-title="Одобрить запись" style="display:none">
            <form action="/articles/approve" class="form modal-window__form" data-auto-close-modal="1">
                <input type="hidden" name="article_id" value="{{$article->id}}"/>
                <div class="modal-window__small-text">
                    @if ($article->pending)
                        Вы уверены, что хотите сделать эту запись публичной?
                    @else
                        Вы уверены, что хотите скрыть эту запись?
                    @endif
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
@section ('scripts')
<script>
    var bb = new bbCodes();
    bb.init('message');
</script>
@endsection
