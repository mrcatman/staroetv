@extends('layouts.default')
@section('page-title')
    Архив телетекста
@endsection
@section('content')
    <div class="box">
        <div class="box__heading">
            <h1 class="box__heading__inner">
                Архив телетекста
            </h1>
            <div class="box__heading__buttons">
                @if (\App\Helpers\PermissionsHelper::allows('teletextown'))
                    <a class="button" href="{{route('teletext.add')}}">
                        <i class="fa fa-plus"></i>
                        Добавить
                    </a>
                @endif
            </div>
        </div>
        <div class="box__inner">
            <div class="text-content">
                <p>
                    Телетекст - это система, позволявшая принимать дополнительную информацию вместе с телевизионным сигналом.
                </p>
                <p>
                    Чаще всего это были новости, программа передач, объявления и другая информация.
                </p>
                <p>
                На большинстве центральных каналов в России в своё время был телетекст.
                </p>
                <br/>
                <p>
                В данном архиве собран телетекст, восстановленный с обычных видеокассет, чаще всего в неидеальном состоянии, хотя есть и приятные исключения.
                </p>

            </div>

        </div>
    </div>

    <div class="box">
        <div class="box__inner">
            <div class="teletext-sections">
                @foreach ($sections as $section)
                    <div class="teletext-section">
                        <a href="{{route('teletext.channel', $section['url'])}}" class="teletext-section__title">
                            {{$section['name']}}
                        </a>
                        <div class="program__channels teletext-section__channels">
                            @foreach ($section['channels'] as $channel)
                                <a href="{{$channel['url']}}" class="program__channel__name">
                                    @if ($channel['logo'])
                                        <img class="program__channel__logo" src="{{$channel['logo']}}"/>
                                    @endif
                                    {{$channel['name']}}
                                </a>
                            @endforeach
                        </div>
                        <div class="records-list records-list--thumbs teletext-section__latest-additions">
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
            @include('blocks.global.digitization')
        </div>
    </div>

@endsection
