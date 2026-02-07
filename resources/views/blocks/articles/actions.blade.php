@if ($can_edit)
    <a class="menu__item button--dropdown__list__item" href="{{route('articles.edit', $article->id)}}">Редактировать</a>
    <a class="menu__item button-dropdown__list__item button--delete-article">Удалить</a>
@endif
@if ($can_approve)
    <a class="menu__item button--dropdown__list__item" data-approve="articles" data-approve-id="{{$article->id}}">{{$article->pending ? "Одобрить" : "Скрыть"}}</a>
@endif


@if ($can_edit)
    <div id="delete_article" data-title="Удалить статью" style="display:none">
        <form action="{{route('articles.delete')}}" class="form modal-window__form" data-auto-close-modal="1">
            <input type="hidden" name="id" value="{{$article->id}}"/>
            <div class="modal-window__text">
                Вы уверены, что хотите удалить статью?
            </div>
            <div class="form__bottom">
                <button class="button button--light">ОК</button>
                <a class="button button--light modal-window__close-button">Отмена</a>
                <div class="response response--light"></div>
            </div>
        </form>
    </div>
@endif
