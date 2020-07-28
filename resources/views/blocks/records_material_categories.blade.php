<div class="material-categories">
    @if ($is_radio)
        <ul class="material-categories__section">
            <li>
                <a href="/radio/programs">Передачи</a>
            </li>
            <li>
                <a href="/radio/jingles">Муз. оформление</a>
            </li>
            <li>
                <a href="/radio/commercials">Реклама</a>
            </li>
        </ul>
    @else
    <ul class="material-categories__section">

        <li class="material-categories__section-heading">
            <a href="/video/programs">Передачи</a>
        </li>
        @foreach (\App\Genre::where(['type' => 'programs'])->get() as $genre)
        <li>
            <a href="/video/programs?category={{$genre->url}}">{{$genre->name}}</a>
        </li>
        @endforeach
        <li>
            <div class="material-categories__delimiter"></div>
        </li>
        <li>
            <a href="/video/programs?category=other">Другое</a>
        </li>
    </ul>
    <ul class="material-categories__section">
        <li class="material-categories__section-heading">
            <a href="/video/commercials">Реклама</a>
        </li>
        <li>
            <a href="/video/commercials?year_end=1991">Советская</a>
        </li>
        <li>
            <a href="/video/commercials?year_start=1992&year_end=1999">90-е годы</a>
        </li>
        <li>
            <a href="/video/commercials?year_start=2000">2000-е годы</a>
        </li>
        <li>
            <div class="material-categories__delimiter"></div>
        </li>
        @foreach (\App\Genre::where(['type' => 'advertising'])->get() as $genre)
            <li>
                <a href="/video/commercials?type={{$genre->url}}">{{$genre->name}}</a>
            </li>
        @endforeach
    </ul>
    <ul class="material-categories__section">
        <li class="material-categories__section-heading">
            <a href="/video/graphics">Графика</a>
        </li>
    </ul>
    @endif
</div>
