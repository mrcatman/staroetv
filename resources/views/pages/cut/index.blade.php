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
            @if ($cut)
            <div class="box__heading__buttons">

                    <span class="button button--light button--dropdown">
                     <span class="button--dropdown__text">Действия</span>
                     <span class="button--dropdown__icon">
                         <i class="fa fa-chevron-down"></i>
                     </span>
                     <span class="menu button--dropdown__list">
                          @if ($cut->download_status === \App\Models\VideoCut::STATUS_SUCCESS)
                             <button class="menu__item button--dropdown__list__item">Перескачать</button>
                         @endif
                           <a class="menu__item button--dropdown__list__item"
                              data-confirm-form-input-value="{{$cut->id}}"
                              data-confirm-form-text="Вы уверены, что хотите удалить эту запись?"
                              data-confirm-form-url="{{route('cut.delete')}}">Удалить</a>
                     </span>
                 </span>
             </div>
            @endif
        </div>
        <div class="box__inner">
            @if ($cut)
                @if ($cut->download_status === \App\Models\VideoCut::STATUS_PENDING)
                    Идет скачивание файла для обрезки, подождите... <br/><br/>
                    <button class="button">Попробовать скачать еще раз</button>
                @elseif ($cut->download_status === \App\Models\VideoCut::STATUS_SUCCESS)
                    <video-cutter
                        :cut='@json($cut)'
                        :channel='@json($channel)'
                        :video='@json($video)'
                    ></video-cutter>
                @elseif ($cut->download_status === \App\Models\VideoCut::STATUS_ERROR)
                    Скачивание файла не удалось: <strong>{{$cut->error}}</strong><br/><br/>
                    <button class="button">Попробовать скачать еще раз</button>
                @endif
            @else
                <div class="response"></div>
                Нажмите на кнопку ниже, чтобы перейти в редактор видео.
                @if ($video && $video->use_own_player)
                    Видео будет скачиваться с видеохостинга, поэтому придется немного подождать.
                @endif
                <br><br/>
                <button class="button">Начать обрезку</button>
            @endif
        </div>
        @csrf
    </form>
@endsection
