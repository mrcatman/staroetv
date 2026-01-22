<div class="material-categories">
    <ul class="material-categories__section">
        <div class="material-categories__button-container">
            <a class="button button--big" href="{{typed_route('records.[RECORD].calendar.index', $is_radio)}}">
                <i class="fa fa-calendar button__icon"></i>
                Календарь записей
            </a>
        </div>
    </ul>
    @if ($is_radio)
        <ul class="material-categories__section">
            <li class="material-categories__item">
                <a class="material-categories__item__link" href="{{route('records.radio.programs')}}">Передачи</a>
            </li>
            <li class="material-categories__item">
                <a class="material-categories__item__link"
                   href="{{route('records.search', ['is_radio' => true, 'type' => 'interprogram'])}}">Муз.
                    оформление</a>
            </li>
            <li class="material-categories__item">
                <a class="material-categories__item__link"
                   href="{{route('records.commercials', ['is_radio' => true])}}">Реклама</a>
            </li>
        </ul>
    @else
        <ul class="material-categories__section">
            <li class="material-categories__section-heading">
                <a class="material-categories__item__link" href="{{route('records.video.programs')}}">Передачи</a>
            </li>
            @foreach (\App\Models\Genre::where(['type' => 'programs'])->get() as $genre)
                <li class="material-categories__item">
                    <a class="material-categories__item__link"
                       href="{{route('records.video.programs', ['category' => $genre->url])}}">{{$genre->name}}</a>
                </li>
            @endforeach
            <li class="material-categories__item">
                <a class="material-categories__item__link"
                   href="{{route('records.video.programs', ['category' => 'other'])}}">Другое</a>
            </li>
        </ul>
        <ul class="material-categories__section">
            <li class="material-categories__section-heading">
                <a class="material-categories__item__link" href="{{route('records.commercials')}}">Реклама</a>
            </li>
            <li class="material-categories__item">
                <a class="material-categories__item__link"
                   href="{{route('records.commercials', ['date' => ['range' => 1, 'year_end' => 1991]])}}">Советская</a>
            </li>
            <li class="material-categories__item">
                <a class="material-categories__item__link"
                   href="{{route('records.commercials', ['date' => ['range' => 1, 'year_start' => 1992, 'year_end' => 1999]])}}">90-е
                    годы</a>
            </li>
            <li class="material-categories__item">
                <a class="material-categories__item__link"
                   href="{{route('records.commercials', ['date' => ['range' => 1, 'year_start' => 2000, 'year_end' => 2010]])}}">2000-е
                    годы</a>
            </li>
            @foreach (\App\Models\Genre::where(['type' => 'advertising'])->get() as $genre)
                <li class="material-categories__item">
                    <a class="material-categories__item__link"
                       href="{{route('records.commercials', ['advertising_type' => $genre->id])}}">{{$genre->name}}</a>
                </li>
            @endforeach
        </ul>
        <ul class="material-categories__section">
            <div class="material-categories__button-container">
                <a class="button button--big" href="{{route('design.channels.index')}}">
                    <i class="fa fa-palette button__icon"></i>
                    Оформление каналов
                </a>
            </div>
            <div class="material-categories__button-container">
                <a class="button button--big" href="{{route('design.programs.channels')}}">
                    <i class="fa fa-tv button__icon"></i>
                    Заставки телепередач
                </a>
            </div>
        </ul>
    @endif
</div>

