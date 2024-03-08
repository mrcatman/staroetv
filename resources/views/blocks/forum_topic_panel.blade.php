@if ($topic->can_edit || $topic->can_delete)
    <span class="button button--light button--dropdown" >
        <span class="button--dropdown__text">Действия</span>
        <span class="button--dropdown__icon">
            <i class="fa fa-chevron-down"></i>
        </span>
        <div class="button--dropdown__list">
            @if ($topic->can_edit)
                <a class="button--dropdown__list__item" href="/forum/edit-topic/{{$topic->id}}">Редактировать тему</a>
            @endif
            @if (\App\Helpers\PermissionsHelper::allows('frreplthr'))
                <a class="button--dropdown__list__item forum-section__move-topic">Переместить</a>
            @endif
            @if ($topic->can_delete)
                <a class="button--dropdown__list__item forum-section__delete-topic">Удалить</a>
            @endif
        </div>
    </span>
@endif


<div id="move_topic" data-title="Переместить тему" style="display:none">
    <form action="/forum/move-topic" class="form modal-window__form" data-reset="1" data-auto-close-modal="1">
        <input type="hidden" name="topic_id" value="{{$topic->id}}"/>
        <div class="input-container">
            <label class="input-container__label">Форум</label>
            <div class="input-container__inner">
                <select name="forum_id" class="select-classic">
                    @foreach (\App\Forum::all() as $forum)
                        <option value="{{$forum->id}}" @if ($forum->parent_id < 1) disabled class="select-classic__option-group-title" @else class="select-classic__option-group-value" @endif>{{$forum->title}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="form__bottom">
            <button class="button button--light">ОК</button>
            <a class="button button--light modal-window__close-button">Отмена</a>
            <div class="response response--light"></div>
        </div>

    </form>
</div>

<div id="delete_topic" data-title="Удалить тему" style="display:none">
    <form action="/forum/delete-topic" class="form modal-window__form" data-auto-close-modal="1">
        <input type="hidden" name="topic_id" value="{{$topic->id}}"/>
        <div class="modal-window__small-text">
            Вы уверены, что хотите удалить тему? Это действие нельзя будет отменить. Возможно, вы хотели переместить тему в Корзину.
        </div>
        <div class="form__bottom">
            <button class="button button--light">ОК</button>
            <a class="button button--light modal-window__close-button">Отмена</a>
            <div class="response response--light"></div>
        </div>

    </form>
</div>
