@extends('layouts.default')
@section('content')
    <div class="inner-page channel-page">
        <div class="breadcrumbs">
            <a class="breadcrumbs__item" href="/{{$channel->is_radio ? "radio" : "video"}}">Архив</a>
            <a class="breadcrumbs__item breadcrumbs__item--current">{{$channel->name}}</a>
        </div>
        <div class="inner-page__header">
            <div class="inner-page__header__title">{{$channel->name}}</div>
            @if ($channel->can_edit)
                <span class="button button--light button--dropdown" >
                    <span class="button--dropdown__text">Опции</span>
                    <span class="button--dropdown__icon">
                        <i class="fa fa-chevron-down"></i>
                    </span>
                    <div class="button--dropdown__list">
                        <a class="button--dropdown__list__item" href="/channels/{{$channel->id}}/edit">Редактировать</a>
                        <a class="button--dropdown__list__item" data-confirm-form-input-name="channel_id" data-confirm-form-input-value="{{$channel->id}}" data-confirm-form-text="Вы уверены, что хотите удалить канал?" data-confirm-form-url="/channels/delete">Удалить</a>
                    </div>
                </span>
            @endif
        </div>
        <div class="inner-page__content">
            <div class="channel-page__top">
                <div class="channel-page__logos">
                    @if (count($channel->names) > 0)
                        <div class="channel-page__selected-logo">
                            <div class="channel-page__selected-logo__picture__container">
                                <div class="channel-page__selected-logo__picture" style="background-image: url({{$channel->names[0]->logo && $channel->names[0]->logo->url ? $channel->names[0]->logo->url : ''}})"></div>
                                <div class="channel-page__selected-logo__picture channel-page__selected-logo__picture--shadow"  style="background-image: url({{$channel->names[0]->logo && $channel->names[0]->logo->url ? $channel->names[0]->logo->url : ''}})"></div>
                            </div>
                            <div class="channel-page__selected-logo__name">{{$channel->names[0]->name}} </div>
                            <div class="channel-page__selected-logo__years">{{$channel->names[0]->years_range}} </div>
                            <div class="channel-page__selected-logo__description">{{$channel->names[0]->comment}}</div>
                        </div>
                        <div class="channel-page__logos__list">
                            <div class="channel-page__logos__list__inner">
                            @foreach($channel->names as $index => $name)
                            @if ($name->logo)
                            <a class="channel-page__logos__list__item @if ($index == 0) channel-page__logos__list__item--selected @endif" data-info="{{$name}}" style="background-image: url({{$name->logo && $name->logo->url ? $name->logo->url : ''}})"></a>
                            @endif
                            @endforeach
                       </div>
                    </div>
                    @elseif ($channel->logo)
                        <div class="channel-page__selected-logo">
                            <div class="channel-page__selected-logo__picture__container">
                                <div class="channel-page__selected-logo__picture" style="background-image: url({{$channel->logo->url}})"></div>
                                <div class="channel-page__selected-logo__picture channel-page__selected-logo__picture--shadow" style="background-image: url({{$channel->logo->url}})"></div>
                            </div>
                        </div>
                   @endif
                </div>
                <div class="channel-page__description">
                    <div class="channel-page__description__text">{!! $channel->description !!}</div>
                    <div class="channel-page__description__params">
                        @if ($channel->is_regional)<div class="page__description__param">Город: <strong>{{$channel->city}}</strong></div> @endif
                    </div>
                    <div class="channel-page__description__params">
                        @if ($channel->is_abroad)<div class="page__description__param">Страна: <strong>{{$channel->country}}</strong></div> @endif
                    </div>
                </div>
            </div>
        </div>
        @php($programs_edit = \App\Helpers\PermissionsHelper::allows('programs'))
        @php($programs_edit_own = \App\Helpers\PermissionsHelper::allows('programsown'))
        @if (count($programs) > 0 || $programs_edit || $programs_edit_own)
        <div class="inner-page__header">
            <div class="inner-page__header__title">Программы канала {{$channel->name}}</div>
            <div class="inner-page__header__right">
                @if ($programs_edit_own)
                    <a href="{{$channel->full_url}}/programs/add" class="button button--light">Добавить</a>
                @endif
                @if ($programs_edit)
                    <a href="{{$channel->full_url}}/programs/edit" class="button button--light">Редактировать список</a>
                @endif
            </div>
        </div>
        @endif
        @if(count($programs) > 0)
        <div class="inner-page__content">
            <div class="channel-page__programs">
                <div class="categories-list">
                    @foreach($programs as $index => $genre)
                         <a data-selector=".category" data-toggle-class="category--active" data-show-block-selector=".programs-list" data-show-block-id="{{$genre->id}}" class="category @if ($index == 0) category--active @endif">{{$genre->name}}</a>
                    @endforeach
                </div>
                @foreach($programs as $index => $genre)
                    <div class="programs-list" data-block-id="{{$genre->id}}" @if ($index != 0) style="display: none" @endif>
                        @foreach ($genre->programs as $program)
                            <a href="/programs/{{$program->id}}" class="program">
                                <span class="program__cover" style="background-image: url({{$program->coverPicture ? $program->coverPicture->url : ''}})"></span>
                                <span class="program__name">{{$program->name}}</span>
                            </a>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
        @endif
        @php($can_edit_interprogram = \App\Helpers\PermissionsHelper::allows('additionalown'))
        @if (count($interprogram_packages) > 0 || $can_edit_interprogram)
            <div class="inner-page__header">
                <div class="inner-page__header__title">Межпрограммное оформление канала {{$channel->name}}</div>
                @if ($can_edit_interprogram)
                    <div class="inner-page__header__right">
                        <a href="{{$channel->full_url}}/graphics/add" class="button button--light">Добавить</a>
                    </div>
               @endif
            </div>
            @if (count($interprogram_packages) > 0)
                <div class="inner-page__content">
                    <div class="interprogram-packages-list">
                        @foreach($interprogram_packages as $package)
                            <a href="/channels/{{$channel->url}}/graphics#package_{{$package->id}}" class="interprogram-package">
                                <div class="interprogram-package__cover">
                                    @if (count($package->pictures) > 0)
                                        @foreach ($package->pictures->take(4) as $picture)
                                            <div class="interprogram-package__cover__picture" style="background-image: url({{$picture->url}})"></div>
                                        @endforeach
                                    @else
                                        @foreach ($package->records->take(4) as $record)
                                            <div class="interprogram-package__cover__picture" style="background-image: url({{$record->cover}})"></div>
                                        @endforeach
                                    @endif
                                </div>
                                <div class="interprogram-package__name">{{$package->name}}</div>
                                <div class="interprogram-package__years">{{$package->years_range}}</div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        @endif
    <div class="row">
        <div class="box">
            <div class="box__heading">
                <div class="box__heading__inner">
                    {{$channel->is_radio ? "Радиозаписи" : "Видеозаписи"}} <span class="box__heading__count">{{$records_count}}</span>
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
