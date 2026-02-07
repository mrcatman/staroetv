<form action="{{route('records.edit.type.save')}}" class="form" data-auto-close-modal="1">
    <div class="form__content">
        <input type="hidden" name="ids" value="{{$records->pluck('id')->join(',')}}"/>

        <div class="form__content" style="width: 100%">
            <type-and-category-transfer/>
        </div>

        <div class="form__bottom">
            <button class="button button--light">Сохранить</button>
            <div class="response response--light"></div>
        </div>
    </div>

</form>
