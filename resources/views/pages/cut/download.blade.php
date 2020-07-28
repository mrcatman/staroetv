@extends('layouts.default')
@section('content')
    <form class="form box" action="/cut/download-external" method="POST">
        <div class="box__heading">
            Загрузить видео для обрезки
        </div>
        <div class="box__inner">
            <div class="response"></div>
            <div class="input-container">
                <label class="input-container__label">URL (поддерживается Youtube, ВК <a href="http://ytdl-org.github.io/youtube-dl/supportedsites.html" target="_blank">и другие сайты</a>)<span class="input-container__required">*</span></label>
                <div class="input-container__inner">
                    <input class="input" name="url" value=""/>
                    <span class="input-container__message"></span>
                </div>
            </div>
            <button class="button">Начать загрузку</button>
        </div>
        @csrf
    </form>
@endsection
