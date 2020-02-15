@extends('layouts.default')
@section('content')
    <div class="inner-page">
        <div class="breadcrumbs">
            <a class="breadcrumbs__item" href="{{$program->channel->is_radio ? "/radio" : "/video"}}">Архив</a>
            <a class="breadcrumbs__item" href="{{$program->channel->full_url}}">{{$program->channel->name}}</a>
            <a class="breadcrumbs__item breadcrumbs__item--current">{{$program->name}}</a>
        </div>
        <div class="inner-page__header">
            <div class="inner-page__header__title">{{$program->name}}</div>
            @if ($program->can_edit)
                <span class="button button--light button--dropdown" >
                    <span class="button--dropdown__text">Опции</span>
                    <span class="button--dropdown__icon">
                        <i class="fa fa-chevron-down"></i>
                    </span>
                    <div class="button--dropdown__list">
                        <a class="button--dropdown__list__item" href="/programs/{{$program->id}}/edit">Редактировать</a>
                        <a class="button--dropdown__list__item" data-confirm-form-input-name="program_id" data-confirm-form-input-value="{{$program->id}}" data-confirm-form-text="Вы уверены, что хотите удалить программу?" data-confirm-form-url="/programs/delete">Удалить</a>
                    </div>
                </span>
            @endif
        </div>
        @if ($program->description != "")
        <div class="inner-page__content">
            <div class="inner-page__text-block">
                {!! $program->description !!}
            </div>
        </div>
        @endif
        <div class="row">
            <div class="box">
                <div class="box__heading">
                    <div class="box__heading__inner">
                        Видео <span class="box__heading__count">{{($records_count)}}</span>
                    </div>

                </div>
                <div class="box__inner">
                    <div class="records-list records-list--thumbs">
                        @foreach($records as $record)
                            @include('blocks/record', ['record' => $record])
                        @endforeach
                    </div>
                    <div class="records-list__pager-container">
                        {{$records->links()}}
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
