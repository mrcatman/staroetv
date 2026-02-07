<form action="{{route('records.edit.transfer.form')}}" class="form" data-auto-close-modal="1">
    <div class="form__content">
        <input type="hidden" name="ids" value="{{$records->pluck('id')->join(',')}}"/>

         <div class="modal-window__text">
            <strong>Текущие значения каналов: </strong>{{$channels}}
        </div>
        <div class="modal-window__text">
            <strong>Текущие значения программ: </strong>{{$programs}}
        </div>

        <div class="form__content" style="width: 100%">
            <channel-and-program-transfer :channel-id="@json($selected_channel_id)" :program-id="@json($selected_program_id)" />
        </div>

        <div class="form__bottom">
            <button class="button button--light">Сохранить</button>
            <div class="response response--light"></div>
        </div>
    </div>

</form>
