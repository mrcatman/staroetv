@extends('layouts.default')
@section('page-title')
    Архив телетекста
@endsection
@section('content')
    <div class="box">
        <div class="box__heading">
            <div class="box__heading__inner">
                Архив телетекста
            </div>
            <div class="box__heading__buttons">
                @if (\App\Helpers\PermissionsHelper::allows('teletextown'))
                    <a class="button" href="/teletext/add">
                        <i class="fa fa-plus"></i>
                        Добавить
                    </a>
                @endif
            </div>
        </div>
        <div class="box__inner">
            Телетекст - это...
        </div>
    </div>

    <div class="box">
        <div class="box__inner">
            <div class="teletext-list">
                @foreach ($sections as $section)
                    <div class="teletext-item">
                        <a href="/teletext/channels/{{$section['url']}}" class="teletext-item__title">
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
                        <div class="records-list records-list--thumbs teletext-item__latest-additions">
                            @foreach($section['items'] as $teletext)
                                @include('blocks.records.item', ['record' => $teletext, 'title' => $teletext->date_formatted])
                            @endforeach
                        </div>


                    </div>
                @endforeach
            </div>
        </div>
    </div>
    <div class="row row--stretch">
        <div class="col col--2-5">
            @include('blocks.teletext.list')
        </div>
        <div class="col col--sidebar">
            @include('blocks/banner')
        </div>
    </div>

@endsection
