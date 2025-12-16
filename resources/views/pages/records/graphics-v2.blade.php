@extends('layouts.default')
@section('page-title')
    Графическое оформление телеканалов
@endsection
@section('content')
    <div class="box">
        <div class="box__heading">
            <div class="box__heading__inner">
                Графическое оформление телеканалов
            </div>
        </div>
    </div>
    @foreach($packages as $channel_packages)
        <div class="box">
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
                        @include('blocks.design.package', ['package' => $package])
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach

@endsection
