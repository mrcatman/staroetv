<form action="/warnings/add" class="form warnings-form" data-reset="1" data-auto-close-modal="1">
    <input type="hidden" name="user_id" value="{{$user_id}}"/>
    <div class="radio-buttons">
        <label class="radio-button">
            <input type="radio" name="weight" value="1"/>
            <div class="radio-button__circle"></div>
            <div class="radio-button__text">повысить уровень замечаний</div>
        </label>
        <label class="radio-button">
            <input type="radio" checked name="weight" value="-1"/>
            <div class="radio-button__circle"></div>
            <div class="radio-button__text">понизить уровень замечаний (снять бан)</div>
        </label>
    </div>
    <div class="input-container input-container--vertical">
        <label class="input-container__label">Введите причину</label>
        <div class="input-container__inner">
            <textarea class="input input--textarea" name="comment"></textarea>
        </div>
    </div>
    <div class="input-container input-container--vertical">
        <label class="input-container__label">Блокировать активность</label>
        <div class="row">
            <div class="col warnings-form__count">
                <div class="input-container__inner">
                    <input type="number" value="0" min="0" class="input warnings-form__count__input" name="count"/>
                </div>
            </div>
            <div class="col">
                <div class="input-container__inner">
                    <select class="select-classic" name="units">
                        <option value="days">дней</option>
                        <option value="hours">часов</option>
                    </select>
                </div>
            </div>
            <div class="col">
                <label class="input-container input-container--checkbox">
                    <input type="checkbox" name="forever"/>
                    <div class="input-container--checkbox__element"></div>
                    <div class="input-container__label">Блокировать навсегда</div>
                </label>
            </div>
        </div>
    </div>
    <div class="form__bottom">
        <button class="button button--light">Применить</button>
        <div class="response response--light"></div>
    </div>
</form>
