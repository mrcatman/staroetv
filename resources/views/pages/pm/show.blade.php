@extends('layouts.default')
@section('content')
    <div class="private-messages private-message-page box">
        <div class="box__heading">
           <div class="box__heading__inner">
               {{$message->title ? $message->title : "Без темы"}}
           </div>
            <div class="box__heading__buttons">
                <a class="button" href="{{route('pm.index')}}">
                    <i class="fa fa-envelope"></i>Список сообщений
                </a>
                <a class="button button--light" href="{{route('pm.add')}}">
                    <i class="fa fa-plus"></i>
                    Написать новое
                </a>
            </div>

        </div>
        <div class="box__inner">
            <div class="private-message-page__info">
                @if ($user) 
                <a href="{{route('users.show', $user->id)}}" class="private-message-page__info__item">
                    <i class="fa fa-user"></i>
                    {{$user->username}}
                </a>
                @endif
                <span class="private-message-page__info__item">
                    <i class="fa fa-clock"></i>
                    {{$message->created_at}}
                </span>
            </div>
            <div class="private-message-page__text">
                {!! $message->text !!}
            </div>
        </div>
    </div>
    @if ($user && !$message->is_out)
        <div class="box">
            <div class="box__heading">Написать ответ</div>
            <form action="{{route('pm.save')}}" class="form box__inner private-message-page__form">
                <div class="private-message-page__form__inner">
                    <input type="hidden" name="to_id" value="{{$user->id}}">
                    <div class="input-container">
                        <label class="input-container__label">Заголовок</label>
                        <div class="input-container__inner">
                            <input class="input" name="title" value=""/>
                            <span class="input-container__message"></span>
                        </div>
                    </div>
                    <div class="input-container">
                        <label class="input-container__label">Текст<span class="input-container__required">*</span></label>
                        <div class="input-container__inner">
                            @include('blocks.bb-editor.main', ['name' => 'text'])
                            <span class="input-container__message"></span>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    @endif
@endsection
@section ('scripts')
    <script>
        window.pm.updateCount();
    </script>
@endsection
