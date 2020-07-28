@if ($can_edit)
<a class="button--dropdown__list__item" href="{{$edit_link}}">Редактировать</a>
@if ($article->type_id != \App\Article::TYPE_ARTICLES)
<a class="button--dropdown__list__item" data-change-article-type="{{\App\Article::TYPE_ARTICLES}}" data-change-article-type-id="{{$article->id}}">Переместить в "Статьи"</a>
@endif
@if ($article->type_id != \App\Article::TYPE_NEWS)
    <a class="button--dropdown__list__item" data-change-article-type="{{\App\Article::TYPE_NEWS}}" data-change-article-type-id="{{$article->id}}">Переместить в "Новости"</a>
@endif
@if ($article->type_id != \App\Article::TYPE_BLOG)
    <a class="button--dropdown__list__item" data-change-article-type="{{\App\Article::TYPE_BLOG}}" data-change-article-type-id="{{$article->id}}">Переместить в "Блог"</a>
@endif
@endif
@if ($can_delete)
<a class="button--dropdown__list__item button--delete-article">Удалить</a>
@endif
@if ($can_approve)
<a class="button--dropdown__list__item" data-approve="articles" data-approve-id="{{$article->id}}">{{$article->pending ? "Одобрить" : "Скрыть"}}</a>
@endif


@if ($can_delete)
    <div id="delete_article" data-title="Удалить запись" style="display:none">
        <form action="/articles/delete" class="form modal-window__form" data-auto-close-modal="1">
            <input type="hidden" name="article_id" value="{{$article->id}}"/>
            <div class="modal-window__small-text">
                Вы уверены, что хотите удалить запись?
            </div>
            <div class="form__bottom">
                <button class="button button--light">ОК</button>
                <a class="button button--light modal-window__close-button">Отмена</a>
                <div class="response response--light"></div>
            </div>
        </form>
    </div>
@endif
