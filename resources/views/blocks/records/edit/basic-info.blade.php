<form action="{{route('records.edit.basic-info.save')}}" class="form" data-auto-close-modal="1">
    <div class="form__content">
        <input type="hidden" name="id" value="{{$record->id}}" />
        <div class="input-container input-container--vertical">
            <label class="input-container__label">Дата</label>
            <div class="input-container__inner">
                <date-select name="date" :date='@json(['year' => $record->year, 'month' => $record->month, 'day' => $record->day])'></date-select>
            </div>
        </div>

        <div class="input-container input-container--vertical">
            <label class="input-container__label">Заголовок</label>
            <div class="input-container__inner">
                <input class="input" name="title" value="{{$record->title}}" />
            </div>
        </div>
        <div class="input-container input-container--vertical">
            <label class="input-container__label">Короткое описание</label>
            <div class="input-container__inner">
                <input class="input" name="short_description" value="{{$record->short_description}}" />
            </div>
        </div>
        <div class="input-container input-container--vertical">
            <label class="input-container__label">Описание</label>
            <div class="input-container__inner">
                <textarea class="input input--textarea" name="description">{{$record->description}}</textarea>
            </div>
        </div>
        <div class="input-container input-container--vertical">
            <label class="input-container__label">Источник</label>
            <div class="input-container__inner">
                <input class="input" name="source" value="{{$record->source}}" />
            </div>
        </div>
        <div class="form__bottom">
            <button class="button button--light">Сохранить</button>
            <div class="response response--light"></div>
        </div>
    </div>

</form>
