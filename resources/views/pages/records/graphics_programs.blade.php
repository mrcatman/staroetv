@extends('layouts.default')
@section('page-title')
    Оформление телевизионных программ
@endsection
@section('content')
    <div class="inner-page interprogram-index-page">
        <div class="row row--align-start">
            <div class="col col--2-5">
                <div class="inner-page__header">
                    <div class="inner-page__header__title">Оформление телевизионных программ</div>
                </div>
                <div class="inner-page__content inner-page__content--no-padding">
                    @foreach($programs as $programs_list)
                    <div class="box">
                        <div class="box__heading">
                            <div class="box__heading__inner">
                                {{$programs_list[0]->channel->name}} @if ($programs_list[0]->channel->is_abroad) ({{$programs_list[0]->channel->country}}) @endif @if ($programs_list[0]->channel->is_regional) ({{$programs_list[0]->channel->city}}) @endif
                            </div>
                        </div>
                        <div class="box__inner">
                            <div class="programs-list">
                                @foreach ($programs_list as $program)
                                    @include('blocks.program', ['url' => $program->full_url.'/graphics'])
                                @endforeach

                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            <!--
            <div class="col col--sidebar">
            </div>
            -->
        </div>

    </div>
@endsection
