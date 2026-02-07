<form action="{{route('records.edit.commercials-info.save')}}" class="form" data-auto-close-modal="1">
    <div class="form__content">


        <div class="form__content" style="width: 100%">
            <commercials-info-editor :record='@json($record)' />
        </div>


        <div class="form__bottom">
            <button class="button button--light">Сохранить</button>
            <div class="response response--light"></div>
        </div>
    </div>

</form>
