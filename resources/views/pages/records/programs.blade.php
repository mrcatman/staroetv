@extends('layouts.default')
@section('content')
    <div class="inner-page">
        <div class="inner-page__content">
            <div class="box">
                <div class="box__heading">
                    <div class="box__heading__inner">{{$page_title}}</div>
                </div>
                <div class="box__inner">
                     <div class="channel-page__programs">
                        <div class="programs-list @if (count($records_conditions['program_id_in']) > 20) programs-list--with-show-all @endif">
                            @include('blocks/programs_list', ['programs' => $programs])
                            @if (count($records_conditions['program_id_in']) > 20)
                                <div class="programs-list__show-all"><a data-is-radio="{{$params['is_radio']}}" data-category="{{$category ? $category->url : null}}" class="button">Показать все</a></div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @include('blocks/records_list', ['conditions' => $records_conditions])
        </div>
    </div>
@endsection
