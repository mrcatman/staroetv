@extends('layouts.default')
@section('title')
    {{$page->title}}
@endsection
@section('content')
    <div class="inner-page">
        <div class="box">
            <div class="box__heading">
                <div class="box__heading__inner">
                    {{$page->title}}
                </div>
                @if (\App\Helpers\PermissionsHelper::allows('sipedt') || \App\Helpers\PermissionsHelper::allows('sipdel'))
                    <div class="box__heading__right">

                        <span class="button button--light button--dropdown">
                        <span class="button--dropdown__text">Действия</span>
                        <span class="button--dropdown__icon">
                            <i class="fa fa-chevron-down"></i>
                        </span>
                        <div class="menu button--dropdown__list">
                            @if (\App\Helpers\PermissionsHelper::allows('sipedt'))
                                <a class="menu__item button--dropdown__list__item" href="{{route('pages.edit', $page)}}">Редактировать</a>
                            @endif
                            @if (\App\Helpers\PermissionsHelper::allows('sipdel'))
                                <a class="menu__item button-dropdown__list__item button--delete-page">Удалить</a>
                            @endif
                        </div>
                    </span>

                    </div>
                @endif
            </div>
            <div class="box__inner">
                <div class="text-content static-page @if ($page->url) static-page--{{$page->url}} @endif">
                    {!! $page->fixed_content !!}
                </div>
            </div>

        </div>
    </div>
    @if (\App\Helpers\PermissionsHelper::allows('sipdel'))
        <div id="delete_page" data-title="Удалить страницу" style="display:none">
            <form action="{{route('pages.delete')}}" class="form modal-window__form" data-auto-close-modal="1">
                <input type="hidden" name="id" value="{{$page->id}}"/>
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
