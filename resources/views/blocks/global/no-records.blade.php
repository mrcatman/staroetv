<div class="nothing-found">
    В данный момент записей нет. Вы можете помочь сайту, <a @if (auth()->user()) href="{{typed_route('records.[RECORD].add', $is_radio)}}" @else class="button--login" @endif>добавив свои записи</a>
</div>
