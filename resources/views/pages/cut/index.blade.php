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
                @if ($cut->download_status === -1)
                    <p class="page-text">Идет скачивание файла для обрезки, подождите... </p>
                    <button class="button">Попробовать скачать еще раз</button>
                @elseif ($cut->download_status === 1)

                    <video-cutter
                        :cut='@json($cut)'
                        :channel='@json($channel)'
                        :video='@json($video)'
                    ></video-cutter>
                @elseif ($cut->download_status === 0)
                    <p class="page-text">Скачивание файла не удалось</p>
                    <button class="button">Попробовать скачать еще раз</button>
                @endif
            @else
                <div class="response"></div>
                @if ($video && $video->use_own_player)
                    <p class="page-text">Нажмите на кнопку ниже, чтобы перейти в редактор видео.<br></p>
                @else
                    <p class="page-text">Нажмите на кнопку ниже, чтобы перейти в редактор видео. Видео будет скачиваться
                        с видеохостинга, поэтому придется немного подождать.<br></p>
                @endif
                <br>
                <button class="button">Начать обрезку</button>
            @endif
        </div>
        @csrf
    </form>
@endsection
