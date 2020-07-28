@extends('layouts.default')
@section('content')
    <div class="inner-page advertising-list-page">
        <div class="inner-page__header">
            <div class="inner-page__header__title">{{$category->name}}</div>
            <div class="inner-page__header__right">

            </div>
        </div>
        <div class="inner-page__content inner-page__content--no-padding">
            <div class="box">
                <div class="box__inner">
                     <div class="channel-page__programs">
                        <div class="programs-list programs-list--auto-hide">
                            @foreach ($programs as $program)
                                <div class="program">
                                    <a href="/programs/{{$program->id}}" class="program__cover" style="background-image: url({{$program->coverPicture ? $program->coverPicture->url : ''}})"></a>
                                    <a href="/programs/{{$program->id}}" class="program__name">{{$program->name}}</a>
                                    <div class="program__channels">
                                        @foreach ($program->channels_history as $program_channel)
                                        <a href="{{$program_channel['url']}}" class="program__channel__name">
                                            @if ($program_channel['logo'])
                                            <img class="program__channel__logo" src="{{$program_channel['logo']}}"/>
                                            @endif
                                            {{$program_channel['name']}}
                                        </a>
                                        @endforeach
                                    </div>
                                </div>
                                @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @include('blocks/records_list', ['class' => 'records-list__outer--full-page', 'conditions' => $records_conditions])
        </div>
    </div>
@endsection
