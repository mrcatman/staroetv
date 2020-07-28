@extends('layouts.default')
@section('content')
    <div class="inner-page">
        <div class="inner-page__header">
            <div class="inner-page__header__title">{{$page->title}}</div>
            <div class="inner-page__header__right">
                @if (\App\Helpers\PermissionsHelper::allows('sipedt') || \App\Helpers\PermissionsHelper::allows('sipdel'))
                    <span class="button button--light button--dropdown">
                        <span class="button--dropdown__text">Действия</span>
                        <span class="button--dropdown__icon">
                            <i class="fa fa-chevron-down"></i>
                        </span>
                        <div class="button--dropdown__list">
                            @if (\App\Helpers\PermissionsHelper::allows('sipedt'))
                                <a class="button--dropdown__list__item" href="/pages/{{$page->id}}/edit">Редактировать</a>
                            @endif
                            @if (\App\Helpers\PermissionsHelper::allows('sipdel'))
                                 <a class="button--dropdown__list__item button--delete-page">Удалить</a>
                            @endif
                        </div>
                    </span>
                @endif
            </div>
        </div>
        <div class="inner-page__content">
            <div class="inner-page__text-block inner-page__text-block--static">
                {!! $page->content !!}
            </div>
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
