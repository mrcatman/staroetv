@extends('layouts.default')
@section('content')
    <div class="inner-page channel-page">
        <div class="breadcrumbs">
            <a class="breadcrumbs__item" href="/{{$channel->is_radio ? "radio" : "video"}}">Архив</a>
            <a class="breadcrumbs__item" href="{{$channel->full_url}}">{{$channel->name}}</a>
            <a class="breadcrumbs__item breadcrumbs__item--current">Графика</a>
        </div>
        <div class="inner-page__header">
            <div class="inner-page__header__title">Графическое оформление канала {{$channel->name}}</div>
            @if(\App\Helpers\PermissionsHelper::allows('additionalown'))
            <a class="button" href="/channels/{{$channel->id}}/graphics/add">Добавить пакет</a>
            @endif
         </div>
        <div class="inner-page__content">
           @foreach ($packages as $package)
                <div class="graphics-package">
                    <div class="graphics-package__date">{{$package->years_range}}</div>
                    <div class="graphics-package__author">Автор: <strong>{{$package->author}}</strong></div>
                    @if ($package->can_edit)
                        <div class="graphics-package__options">
                            <span class="button button--light button--dropdown" >
                                <span class="button--dropdown__text">Опции</span>
                            <span class="button--dropdown__icon">
                                    <i class="fa fa-chevron-down"></i>
                                </span>
                            <div class="button--dropdown__list">
                                <a class="button--dropdown__list__item" href="/channels/{{$channel->id}}/graphics/edit/{{$package->id}}">Редактировать</a>
                                <a class="button--dropdown__list__item" data-confirm-form-input-name="package_id" data-confirm-form-input-value="{{$package->id}}" data-confirm-form-text="Вы уверены, что хотите удалить пакет?" data-confirm-form-url="/graphics/delete">Удалить</a>
                            </div>
                            </span>
                        </div>
                    @endif
                    <div class="graphics-package__description">{{$package->description}}</div>
                    <div class="graphics-package__videos">
                        <div class="small-videos-list">
                            @foreach($package->records_list as $record)
                                @include('blocks/video_small', ['video' => $record])
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
