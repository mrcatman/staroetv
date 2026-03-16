@extends('layouts.default')
@php($title = $is_radio ? 'Музыкальное оформление радиопередач' : 'Графическое оформление телепередач')
@section('page-title')
    {{$title}}
@endsection
@section('content')
<div class="col">
    <div class="box">
                    <div class="box__breadcrumbs">
                                <div class="breadcrumbs">
                                    <a class="breadcrumbs__item" href="{{typed_route('records.[RECORD].index', $is_radio)}}">Архив</a>
                                </div>
                            </div>
                    <div class="box__heading">
                        <div class="box__heading__inner">
                            {{$title}}
                        </div>
                    </div>
                </div>
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
                                    @include('blocks.programs.item', ['url' => route('design.programs.show', $program)])
                                @endforeach

                            </div>
                        </div>
                    </div>
                    @endforeach
            </div>


@endsection
