@extends('layouts.default')
@section('page-title')
    Графическое оформление телеканалов
@endsection
@section('content')
    <div class="inner-page interprogram-index-page">
        <div class="row row--align-start">
            <div class="col col--2-5">
                <div class="box">
                    <div class="box__heading">
                        <div class="box__heading__inner">Графическое оформление телеканалов</div>
                    </div>
                </div>

                @foreach($packages as $channel_packages)
                    <div class="box" id="channel_{{$channel_packages[0]->channel->id}}">
                        <div class="box__heading">
                            <div class="box__heading__inner">
                                {{$channel_packages[0]->channel->name}} @if ($channel_packages[0]->channel->is_abroad)
                                    ({{$channel_packages[0]->channel->country}})
                                @endif @if ($channel_packages[0]->channel->is_regional)
                                    ({{$channel_packages[0]->channel->city}})
                                @endif
                            </div>
                        </div>
                        <div class="box__inner">
                            <div class="interprogram-packages-list">
                                @foreach($channel_packages as $package)
                                    @include('blocks.design.package')
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>


            <div class="col col--sidebar">
                <div class="box">
                    <div class="box__heading">
                        <div class="box__heading__inner">Навигация</div>
                    </div>
                    <div class="box__inner">
                        <ul class="material-categories__section">
                            @foreach($packages as $channel_packages)
                                <li class="material-categories__item">
                                    <a href="#channel_{{$channel_packages[0]->channel->id}}"
                                       class="material-categories__item__link">{{$channel_packages[0]->channel->name}}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>

@endsection
