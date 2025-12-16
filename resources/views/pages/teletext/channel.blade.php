@extends('layouts.default')
@section('page-title')
    {{$title}}
@endsection
@section('content')
    <div class="record-page teletext-page">
        <div class="box">
            <div class="box__breadcrumbs">
                <a class="breadcrumbs__item" href="{{route('teletext.index')}}">Архив телетекста</a>
                <a class="breadcrumbs__item" href="{{route('teletext.channel', $url)}}">{{$title}}</a>
            </div>
            <div class="box__heading">
                <div class="box__heading__inner">
                    {{$title}}
                </div>
            </div>
        </div>
        <div class="row row--stretch record-page__content">
            <div class="col col--3 teletext-page__content">
                @include('blocks.teletext.list')
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
                                @foreach ($related as $teletext)
                                    @include('blocks.teletext.item')
                                @endforeach

                            </div>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>
@endsection
