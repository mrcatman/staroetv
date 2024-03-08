@extends('layouts.default')
@section('content')
    <div class="inner-page advertising-list-page">
        <div class="inner-page__header">
            <div class="inner-page__header__title">{{$page_title}}</div>
            <div class="inner-page__header__right">

            </div>
        </div>
        <div class="inner-page__content inner-page__content--no-padding">
            <div class="box">
                <div class="box__inner">
                     <div class="channel-page__programs">
                        <div class="programs-list">
                            @include('blocks/programs_list', ['programs' => $programs])
                            @if (count($records_conditions['program_id_in']) > 15)
                                <div class="programs-list programs-list--all"></div>
                                <div class="programs-list__show-all"><a data-is-radio="{{$params['is_radio']}}" data-category="{{$category ? $category->url : null}}" class="button">Показать все</a></div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @include('blocks/records_list', ['class' => 'records-list__outer--full-page', 'conditions' => $records_conditions])
        </div>
    </div>
@endsection
