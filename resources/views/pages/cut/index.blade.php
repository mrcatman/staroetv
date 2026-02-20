@extends('layouts.default', ['vue' => true])
@section('content')
    <form class="form box" @if ($video) action="{{route('cut.start', $video)}}" @endif method="POST">
        <div class="box__heading">
            <div class="box__heading__inner">
                Обрезка видео
                @if ($video)
                    {{$video->title}}
                @endif
            </div>
        </div>
        <div class="box__inner">
            @if ($cut)
                @if ($cut->download_status === \App\Models\VideoCut::STATUS_PENDING)
                    Идет скачивание файла для обрезки, подождите... <br/>
                    <button class="button">Попробовать скачать еще раз</button>
                @elseif ($cut->download_status === \App\Models\VideoCut::STATUS_SUCCESS)
                    <video-cutter
                        :cut='@json($cut)'
                        :channel='@json($channel)'
                        :video='@json($video)'
                    ></video-cutter>
                @elseif ($cut->download_status === \App\Models\VideoCut::STATUS_ERROR)
                    Скачивание файла не удалось: <strong>{{$cut->error}}</strong><br/>
                    <button class="button">Попробовать скачать еще раз</button>
                @endif
            @else
                <div class="response"></div>
                Нажмите на кнопку ниже, чтобы перейти в редактор видео.
                @if ($video && $video->use_own_player)
                    Видео будет скачиватьсяс видеохостинга, поэтому придется немного подождать.
                @endif
                <br>
                <button class="button">Начать обрезку</button>
            @endif
        </div>
        @csrf
    </form>
@endsection
