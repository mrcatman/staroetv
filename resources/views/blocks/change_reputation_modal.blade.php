<div id="change_reputation" data-title="Изменить репутацию" style="display:none">
    <form action="/reputation/change" class="form change-reputation" data-reset="1" data-auto-close-modal="1">
        <input type="hidden" name="user_id" value=""/>
        <input type="hidden" name="forum_message_id" value=""/>
        <div class="radio-buttons">
            <label class="radio-button">
                <input type="radio" name="action" value="0"/>
                <div class="radio-button__circle"></div>
                <div class="radio-button__text">не изменять репутацию</div>
            </label>
            <label class="radio-button">
                <input type="radio" checked name="action" value="1"/>
                <div class="radio-button__circle"></div>
                <div class="radio-button__text">повысить репутацию</div>
            </label>
            <label class="radio-button">
                <input type="radio" name="action" value="-1"/>
                <div class="radio-button__circle"></div>
                <div class="radio-button__text">понизить репутацию</div>
            </label>
        </div>
        <div class="input-container input-container--vertical">
            <label class="input-container__label">Комментарий</label>
            <div class="input-container__inner">
                <textarea class="input input--textarea" name="comment"></textarea>
            </div>
        </div>
        <div class="form__bottom">
            <button class="button button--light">Применить</button>
            <div class="response response--light"></div>
        </div>

    </form>
</div>