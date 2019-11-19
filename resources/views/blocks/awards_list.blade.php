<div class="awards-list">
    <div class="awards-list__inner">
        @foreach ($awards as $award)
            <a class="awards-list__item" data-id="{{$award->id}}">
                <img class="awards-list__item__picture" src="{{$award->picture->url}}"/>
            </a>
        @endforeach
    </div>
    <form action="/awards/give-out" class="form awards-list__form" style="display: none" data-reset="1" data-auto-close-modal="1">
        <input type="hidden" name="award_id" value="" />
        @if (isset($user_id))<input type="hidden" name="user_id" value="{{$user_id}}" />@endif
        <div class="input-container input-container--vertical">
            <label class="input-container__label">Комментарий</label>
            <div class="input-container__inner">
                <textarea class="input input--textarea" name="comment"></textarea>
            </div>
        </div>
        <div class="form__bottom">
            <button class="button button--light">Выдать награду</button>
            <div class="response response--light"></div>
        </div>
    </form>
</div>