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
                            <div class="remote__channel__logo" @if ($channel->logo) style="background-image:url(https://staroetv.su/{{$channel->logo->url}})"  @endif></div>
                            <span class="remote__channel__name" >{{$channel->name}}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="tv">
        <div class="tv__inner">
            <div class="tv__overlay" id="overlay">
                <div class="tv__overlay__channel" id="channel"></div>
                <div class="tv__overlay__program" id="program"></div>
            </div>
            <img src="/pictures/tv.png?1" class="tv__picture" />
            <div class="tv__noise" id="noise"></div>
            <div id="player" class="tv__player"></div>
        </div>
    </div>
    <div class="floor">
        <div class="floor__inner">
            <img class="floor__picture" src="/pictures/floor.png" />
        </div>

    </div>
    <div class="tapes">
        @foreach($programs as $program)
            <div class="tape">
                <div class="tape__sticker">
                    <div class="tape__sticker__content">
                        @if ($program->cover) <div class="tape__sticker__cover" style="background-image:url({{$program->cover}})" ></div>@endif

                        {{$program->name}}
                    </div>
                </div>
            </div>
        @endforeach

    </div>
</div>
</body>
@routes
@vite(['resources/js/promo/index.ts'])
</html>
