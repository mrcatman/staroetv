@extends('layouts.admin')
@section('admin_content')
    <div class="admin-panel__heading-container">
        <div class="admin-panel__heading">Страницы сайта</div>
    </div>
    <div class="admin-panel__main-content">
       <table class="admin-panel__table">
           <thead>
                <tr>
                    <td>ID</td>
                    <td>Название страницы</td>
                    <td>Дата изменения</td>
                    <td></td>
                </tr>
           </thead>
           <tbody>
                @foreach ($static_pages as $page)
                <tr>
                    <td>
                        {{$page->id}}
                    </td>
                    <td>
                        {{$page->title}}
                    </td>
                    <td>
                        {{$page->updated_at}}
                        @if ($page->last_updated_by)<span>(пользователь: <strong>{{$page->last_updated_by}}</strong>)</span>@endif
                    </td>
                    <td>
                        <a target="_blank" class="button button--light" href="{{$page->full_url}}">Просмотреть</a>
                        <a target="_blank" class="button button--light" href="/pages/{{$page->id}}/edit">Редактировать</a>
                        <a target="_blank" class="button button--light button--delete-page" data-id="{{$page->id}}">Удалить</a>
                    </td>
                </tr>
                @endforeach
           </tbody>
       </table>
    </div>

    <div id="delete_page" data-title="Удалить страницу" style="display:none">
        <form action="/pages/delete" class="form modal-window__form" data-auto-close-modal="1">
            <input type="hidden" name="page_id" value=""/>
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
@endsection