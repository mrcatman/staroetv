@extends('layouts.default')
@section('content')
    <div class="inner-page">
        <div class="inner-page__header">
            <div class="inner-page__header__title">{{$page->title}}</div>
        </div>
        <div class="inner-page__content">
            <div class="inner-page__text-block inner-page__text-block--static">
                {!! $page->content !!}
            </div>
        </div>
        <div class="inner-page__footer">
            @if (\App\Helpers\PermissionsHelper::allows('sipadd'))
                <a class="button button--light" href="/pages/add">Добавить страницу</a>
            @endif
            @if (\App\Helpers\PermissionsHelper::allows('sipedt'))
                <a class="button button--light" href="/pages/{{$page->id}}/edit">Редактировать</a>
            @endif
            @if (\App\Helpers\PermissionsHelper::allows('sipdel'))
                <a class="button button--light button--delete-page" >Удалить</a>
            @endif
        </div>
    </div>
    @if (\App\Helpers\PermissionsHelper::allows('sipdel'))
        <div id="delete_page" data-title="Удалить страницу" style="display:none">
            <form action="/pages/delete" class="form modal-window__form" data-auto-close-modal="1">
                <input type="hidden" name="page_id" value="{{$page->id}}"/>
                <div class="modal-window__small-text">
                    Вы уверены, что хотите удалить страницу?
                </div>
                <div class="form__bottom">
                    <button class="button button--light">ОК</button>
                    <a class="button button--light modal-window__close-button">Отмена</a>
                    <div class="response response--light"></div>
                </div>
            </form>
        </div>
    @endif
@endsection