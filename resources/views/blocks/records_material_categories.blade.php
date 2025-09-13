<div class="material-categories">
    @if ($is_radio)
        <ul class="material-categories__section">
            <li class="material-categories__item">
                <a class="material-categories__item__link" href="/radio/programs">Передачи</a>
            </li>
            <li class="material-categories__item">
                <a class="material-categories__item__link" href="/radio/jingles">Муз. оформление</a>
            </li>
            <li class="material-categories__item">
                <a class="material-categories__item__link" href="/radio/commercials">Реклама</a>
            </li>
        </ul>
    @else
        <ul class="material-categories__section">
            <a class="button button--big" href="/video/calendar">
                <i class="fa fa-calendar button__icon"></i>
                Календарь записей
            </a>
        </ul>
        <ul class="material-categories__section">
            <li class="material-categories__section-heading">
                <a class="material-categories__item__link" href="/video/programs">Передачи</a>
            </li>
            @foreach (\App\Models\Genre::where(['type' => 'programs'])->get() as $genre)
                <li class="material-categories__item">
                    <a class="material-categories__item__link" href="/video/programs?category={{$genre->url}}">{{$genre->name}}</a>
                </li>
            @endforeach
            <li class="material-categories__item">
                <a class="material-categories__item__link" href="/video/programs?category=other">Другое</a>
            </li>
        </ul>
        <ul class="material-categories__section">
            <li class="material-categories__section-heading">
                <a class="material-categories__item__link" href="/video/commercials">Реклама</a>
            </li>
            <li class="material-categories__item">
                <a class="material-categories__item__link" href="/video/commercials?year_end=1991">Советская</a>
            </li>
            <li class="material-categories__item">
                <a class="material-categories__item__link" href="/video/commercials?year_start=1992&year_end=1999">90-е годы</a>
            </li>
            <li class="material-categories__item">
                <a class="material-categories__item__link" href="/video/commercials?year_start=2000&year_end=2009">2000-е годы</a>
            </li>
            @foreach (\App\Models\Genre::where(['type' => 'advertising'])->get() as $genre)
                <li class="material-categories__item">
                    <a class="material-categories__item__link" href="/video/commercials?type={{$genre->url}}">{{$genre->name}}</a>
                </li>
            @endforeach
        </ul>
        <ul class="material-categories__section">
            <a class="button button--big" href="/video/graphics">
                <i class="fa fa-palette button__icon"></i>
                Оформление каналов
            </a>
        </ul>
    @endif
</div>

