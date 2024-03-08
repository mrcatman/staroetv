@extends('layouts.default')
@section('page-title')
    Графическое оформление телеканалов
@endsection
@section('content')
    <div class="inner-page interprogram-index-page">
        <div class="row row--align-start">
            <div class="col col--2-5">
                <div class="inner-page__header">
                    <div class="inner-page__header__title">Графическое оформление телеканалов</div>
                </div>
                <div class="inner-page__content inner-page__content--no-padding">
                    @foreach($packages as $channel_packages)
                    <div class="box">
                        <div class="box__heading">
                            <div class="box__heading__inner">
                                {{$channel_packages[0]->channel->name}} @if ($channel_packages[0]->channel->is_abroad) ({{$channel_packages[0]->channel->country}}) @endif @if ($channel_packages[0]->channel->is_regional) ({{$channel_packages[0]->channel->city}}) @endif
                            </div>
                        </div>
                        <div class="box__inner">
                            <div class="interprogram-packages-list">
                                @foreach($channel_packages as $package)
                                    @include('blocks/interprogram_package', ['package' => $package])
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
