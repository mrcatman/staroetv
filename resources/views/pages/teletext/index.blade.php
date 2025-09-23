@extends('layouts.default')
@section('page-title')
    Архив телетекста
@endsection
@section('content')
    <div class="inner-page">
        <div class="box box--top">
            <div class="box__heading">
                <div class="box__heading__inner">
                    Архив телетекста
                </div>
                <div class="box__heading__right">
                    @if (\App\Helpers\PermissionsHelper::allows('teletextown'))
                       <a class="button" href="/teletext/add">Добавить телетекст</a>
                    @endif
                </div>
            </div>
        </div>

        <div class="inner-page__content inner-page__content--no-padding">
            <div class="box">
                <div class="box__inner">
                    <div class="programs-list">
                        @foreach ($sections as $section)
                            <div class="teletext-item">
                                <a href="#" class="teletext-item__title">
                                    {{$section['name']}}
                                </a>
                                <div class="program__channels teletext-item__channels">
                                    @foreach ($section['channels'] as $channel)
                                        <a href="{{$channel['url']}}" class="program__channel__name">
                                            @if ($channel['logo'])
                                                <img class="program__channel__logo" src="{{$channel['logo']}}"/>
                                            @endif
                                            {{$channel['name']}}
                                        </a>
                                    @endforeach
                                </div>
                                <div class="teletext-item__latest-additions">
                                    @foreach($section['items'] as $teletext)
                                        @include('blocks/record', ['record' => $teletext])
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="row row--stretch">
                <div class="col col--2-5">
                    <div class="box">
                        <div class="box__inner">

                        </div>
                    </div>

                    <div class="box box--dark">
                        <div class="box__heading">
                            <div class="box__heading__inner">
                                Последние добавления в архив
                            </div>
                        </div>
                        <div class="box__inner">
                            <div class="records-list  records-list--thumbs">
                                @foreach($new as $teletext)
                                    @include('blocks/record', ['record' => $teletext])
                                @endforeach
                            </div>
                            <div class="records-list__pager-container">
                                {{$new->links()}}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="box">
                        <div class="box__inner">

                        </div>
                    </div>
                    @include('blocks/banner')
                </div>
            </div>
        </div>
    </div>

@endsection
