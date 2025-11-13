@extends('layouts.default')
@section('page-title')
    {{$teletext->title}}
@endsection
@section('content')
    <div class="record-page teletext-page">
        <div class="box">
            <div class="box__breadcrumbs">
                <a class="breadcrumbs__item" href="/teletext">Архив телетекста</a>
                @if ($breadcrumb)
                    <a class="breadcrumbs__item" href="{{$breadcrumb['url']}}">{{$breadcrumb['name']}}</a>

                @endif
            </div>
            <div class="box__heading">
                <div class="box__heading__inner">
                    {{$teletext->title}}
                </div>
                <div class="box__heading__right">
                    @if ($teletext->can_edit || \App\Helpers\PermissionsHelper::allows('contentapprove'))
                        <span class="button button--light button--dropdown">
                        <span class="button--dropdown__text">Действия</span>
                        <span class="button--dropdown__icon">
                            <i class="fa fa-chevron-down"></i>
                        </span>
                        <div class="button--dropdown__list">
                            @if (\App\Helpers\PermissionsHelper::allows('contentapprove'))
                                <a class="button--dropdown__list__item" data-approve="teletext"
                                   data-approve-id="{{$teletext->id}}">{{$teletext->pending ? "Одобрить" : "Скрыть"}}</a>
                            @endif
                            @if ($teletext->can_edit)
                                <a class="button--dropdown__list__item"
                                   href="/teletext/{{$teletext->id}}/edit">Редактировать</a>
                                <a class="button--dropdown__list__item"
                                   data-confirm-form-input-value="{{$teletext->id}}"
                                   data-confirm-form-text="Вы уверены, что хотите удалить этот телетекст?"
                                   data-confirm-form-url="/teletext/delete">Удалить</a>
                            @endif
                        </div>
                    </span>
                    @endif
                </div>
            </div>
        </div>
        <div class="row row--stretch record-page__content">
            <div class="col col--3 teletext-page__content">
                <div class="inner-page__content">
                    @include('blocks.teletext-player')

                    <div class="box">
                        <div class="box__inner">
                            <div class="record-page__bottom">
                                @include('blocks.teletext-info')
                            </div>

                            @if($teletext->description != '')
                                <div class="record-page__description">
                                    {!! str_replace(PHP_EOL, "<br>", $teletext->description) !!}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                @include('blocks/comments', ['class' => 'record-page__comments', 'ajax' => false, 'page' => 1, 'conditions' => ['material_type' => \App\Models\Teletext::TYPE_TELETEXT, 'material_id' => $teletext->id]])
            </div>
            <div class="col col--sidebar col--1 record-page__related-container">
                @if ($related && count ($related) > 0)
                    <div class="box">
                        <div class="box__heading box__heading--small">
                            <div class="box__heading__inner">
                                Смотрите ещё
                            </div>
                        </div>
                        <div class="box__inner">
                            <div class="record-page__related">
                                @foreach ($related as $item)
                                    @include('blocks/teletext', ['teletext' => $item])
                                @endforeach

                            </div>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>
@endsection
