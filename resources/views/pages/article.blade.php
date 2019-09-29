@extends('layouts.default')
@section('content')
    <div class="inner-page">
        @if ($article->cover != "")
            <div class="inner-page__cover-block" style="background-image:url({{$article->cover}})">
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
                            Комментарии <span class="box__heading__count">{{\App\Comment::where(['material_type' => $article->type_id, 'material_id' => $article->original_id])->count()}}</span>
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
@endsection
@section ('scripts')
<script>
    var bb = new bbCodes();
    bb.init('message');
</script>
@endsection