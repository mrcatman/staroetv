@extends('layouts.default')
@section('content')
    <div class="inner-page channel-page">
        <div class="breadcrumbs">
            <a class="breadcrumbs__item" href="/{{$program->channel->is_radio ? "radio" : "video"}}">Архив</a>
            <a class="breadcrumbs__item" href="{{$program->channel->full_url}}">{{$program->channel->name}}</a>
            <a class="breadcrumbs__item" href="{{$program->full_url}}">{{$program->name}}</a>
            <a class="breadcrumbs__item breadcrumbs__item--current">Оформление</a>
        </div>
        <div class="inner-page__header">
            <div class="inner-page__header__title">Заставки программы {{$program->name}}</div>
            @if(\App\Helpers\PermissionsHelper::allows('additionalown'))
                <a class="button" href="/programs/{{$program->id}}/graphics/add">Добавить</a>
            @endif
        </div>
        <div class="inner-page__content">
            @foreach ($packages as $package)
                <div class="box interprogram-packages-list-item" id="package_{{$package->id}}">
                    <div class="box__heading">
                        <div class="box__heading__inner">
                            {{$package->name != "" ? ($package->name . ($package->years_range != "" ? " (".$package->years_range.")" : "")) : $package->years_range}}&nbsp;&nbsp;
                            @if ($package->author != "")<div class="interprogram-packages-list-item__author"> (Автор: <strong>{{$package->author}}</strong>)</div>@endif
                        </div>
                        <div class="box__heading__right">
                            @if ($package->can_edit && !$package->is_other)
                                <div class="interprogram-packages-list-item__options">
                                    <span class="button button--light button--dropdown" >
                                        <span class="button--dropdown__text">Действия</span>
                                        <span class="button--dropdown__icon">
                                            <i class="fa fa-chevron-down"></i>
                                        </span>
                                        <div class="button--dropdown__list">
                                            <a class="button--dropdown__list__item" href="/programs/{{$program->id}}/graphics/edit/{{$package->id}}">Редактировать</a>
                                            <a class="button--dropdown__list__item" data-confirm-form-input-name="package_id" data-confirm-form-input-value="{{$package->id}}" data-confirm-form-text="Вы уверены?" data-confirm-form-url="/graphics/delete">Удалить</a>
                                        </div>
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="box__inner">
                        <div class="interprogram-packages-list-item__inner">

                            <div class="interprogram-packages-list-item__description">{!! $package->description !!}</div>
                            <div class="interprogram-packages-list-item__videos">
                                <div class="small-videos-list">
                                    @if ($package->visibleRecords &&  count($package->visibleRecords) > 0)
                                        @foreach($package->visibleRecords as $record)
                                            @include('blocks/video_small', ['video' => $record])
                                        @endforeach
                                    @else
                                        @foreach($package->records as $record)
                                            @include('blocks/video_small', ['video' => $record])
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
