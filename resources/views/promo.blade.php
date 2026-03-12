<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Старый Телевизор</title>
    @vite([ 'resources/sass/promo/index.scss'])
</head>
<body>
<div class="main">
    <div class="remote__container">
        <div class="remote">
            <div class="remote__logo">
                @include ('blocks.global.logo')
            </div>
            <div class="remote__inner">
                <div class="remote__random">Случайный канал</div>
                <div class="remote__channels">
                    @foreach($channels as $channel)
                        <div class="remote__channel" data-id="{{$channel->id}}">
                            <div class="remote__channel__logo"
                                 @if ($channel->logo) style="background-image:url(https://staroetv.su/{{$channel->logo->url}})" @endif></div>
                            <span class="remote__channel__name">{{$channel->name}}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>


    <div class="tv">
        <div class="tv__picture"></div>
        <div class="tv__outer">
            <div class="tv__inner">
                <div class="tv__overlay" id="overlay">
                    <div class="tv__overlay__channel" id="channel"></div>
                    <div class="tv__overlay__program" id="program"></div>
                </div>

                <div class="tv__noise" id="noise"></div>
                <div id="player" class="tv__player"></div>
            </div>
        </div>
    </div>
    <div class="tapes" id="tapes"></div>
    <button class="button button--reload-programs" id="reload_programs">Загрузить ещё передачи</div>
</div>
</body>
@routes
@vite(['resources/js/promo/index.ts'])
</html>
