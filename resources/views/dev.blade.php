<html>
<head>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #0f2812;
            background-image: radial-gradient(#1c1c1c, #000000);
            background-position: 0 -49vh;
            background-size: 100vw 100vw;
            background-repeat: no-repeat;
            overflow: hidden;
        }


        .main {
            width: 100vw;
            height: 100vh;
            position: relative;
        }

        .main:before {
            content: '';
            display: block;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('/pictures/bg.png');
            opacity: .75;
            background-size: 10em;
            mix-blend-mode: overlay;
            pointer-events: none;
        }
        .main:after {
            content: '';
            display: block;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 1000 1000' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.65' numOctaves='3' stitchTiles='stitch' /%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)' /%3E%3C/svg%3E");
            background-size: 500px;
            z-index: 1;
            opacity: .1;
            pointer-events: none;
        }
        .remote__container {
            position: absolute;
            z-index: 2;
            top: 0;
            left: 5em;
            height: 100vh;
            overflow: auto;
        }
        .remote {
            margin: 10em 0;
            background: linear-gradient(45deg, #222, #111);
            border-radius: 2em;
            padding: 1em;
            box-shadow: -1em 5em 2em 2em #0000004a;
        }
        .remote__channels {
            display: flex;
            flex-wrap: wrap;
        }

        .remote__channel__name {
            display: none;
        }

        .remote__channel__logo {
            width: 5em;
            height: 5em;
            background-position: center;
            background-repeat: no-repeat;
            background-size: contain;
        }
        .remote__channels {
            gap: 1em;
            width: 22em;
        }

        .remote__channel {
            background: linear-gradient(45deg, #222, #333);
            padding: .5em;
            border-radius: 1em;
            box-shadow: .25em .25em .25em inset, .125em .125em .25em #000;
        }


        .remote__channel__logo {
            background-size: 80%;
        }
        .remote__inner {
            box-shadow: 0 0 1em;
            padding: 1em;
            border-radius: var(--border-radius-standard);
            background: linear-gradient(90deg, #222 30%, #1d1d1d 50%, #222 70%);
            background-size: .4em;
        }
        .remote__logo path {
            fill: #ffffff21;
            stroke: none;
        }

        .remote__logo {
            display: flex;
            align-items: center;
            justify-content: center;
            padding-bottom: 1em;
            filter: drop-shadow(4px 4px 6px #555);
        }

        .tv {
            position: absolute;
            bottom: -1em;
            width: 42vw;
            right: 5em;
            z-index: 1;
        }

        img.tv__picture {
            width: 100%;
        }

        .tv__player {
            position: absolute;
            top: 4em;
            left: 2em;
            width: calc(100% - 4em);
            height: calc(100% - 12em);
            background: radial-gradient(#fff, #000);
            z-index: -1;
        }

        .tv__inner {
            position: relative;
            width: 100%;
            height: 100%;
        }
        .floor {
            position: absolute;
            bottom: -16em;
            left: 0;
            width: 100%;
            z-index: 0;
        }
        .floor__picture {
            width: 100%;
        }
        .floor__inner {
            position: relative;
        }

        .floor__inner:before {
            content: '';
            display: block;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(transparent, black);
            background-position: 0 13em;
        }
        .tv {
            bottom: 1em;
            filter: drop-shadow(-4em -2em 2em rgba(0,0,0,.5));
        }
        .tape {
            width: 36em;
            height: 2.75em;
            border-radius: .2em;
            position: relative;
            background: linear-gradient(90deg, #050505, #222 1%, #050505 10%, #111 95%, #222 97%, #050505);
            padding: .75em;
        }

        .tapes {
            position: absolute;
            z-index: 3;
            bottom: 0;
            left: 5em;
            display: flex;
            flex-direction: column;
            gap: .25em;
            padding: 0 2.5em;
        }

        .tape:before {
            content: '';
            display: block;
            position: absolute;
            top: calc(50% - .125em);
            height: .25em;
            left: 0;
            width: 100%;
            background: linear-gradient(to bottom, #000 0%, transparent 90%, #444);
        }

        .tape__sticker {
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, #111, #222);
            position: relative;
            border-radius: var(--border-radius-small);
            border-top: 1px solid #333;
            border-left: 1px solid #222;
            display: flex;
            justify-content: center;
        }

        .tape__sticker__content {
            width: 90%;
            background: #fff;
            border-radius: var(--border-radius-small);
            padding: .5em;
        }
        .tape__sticker__content {
            font-family: "Lumios Typewriter Used";
            font-size: 1.125em;
            line-height: 1.4;
            display: flex;
            gap: 1em;
            overflow: hidden;
        }

        .tape__sticker__cover {
            width: 4em;
            height: 2.5em;
            margin: -.5em;
            background-size: cover;
            background-position: center;
        }
        .tape:nth-of-type(4n - 2) .tape__sticker {
            transform: rotate(-.5deg);
        }
        .tape:nth-of-type(4n - 1) .tape__sticker {
            transform: rotate(.25deg);
        }
        .tape:nth-of-type(4n) .tape__sticker {
            transform: rotate(.5deg);
        }

        .tape:nth-of-type(5n - 1) {
            transform: translateX(.25em);
        }
        .tape:nth-of-type(5n - 2) {
            transform: translateX(.125em);
        }
        .tape:nth-of-type(5n - 3) {
            transform: translateX(.5em);
        }
        .tape:nth-of-type(5n - 4) {
            transform: translateX(-.25em);
        }
        .tapes {
            gap: .125em;
            height: 100vh;
            overflow: auto;
            left: 31em;
        }

        .remote__random {
            background: linear-gradient(45deg, #b71818, #b61111);
            padding: .5em;
            border-radius: var(--border-radius-standard);
            box-shadow: .25em .25em .25em inset #7f1d1d, .125em .125em 2px .25em #000;
            text-align: center;
            font-family: "Arial Narrow";
            color: #fff;
            margin-bottom: 1em;
            text-transform: uppercase;
            font-weight: bold;
            text-shadow: 0 0 .5em #000;
        }

        .remote__channels {
            width: 20em;
        }
        ::-webkit-scrollbar {
            width: 0;  /* Remove scrollbar space */
            background: transparent;  /* Optional: just make scrollbar invisible */
        }
    </style>
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
                        <div class="remote__channel">
                            <div class="remote__channel__logo" @if ($channel->logo) style="background-image:url({{$channel->logo->url}})"  @endif></div>
                            <span class="remote__channel__name" >{{$channel->name}}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>


    <div class="tv">
        <div class="tv__inner">
            <img src="/pictures/tv.png?1" class="tv__picture" />
            <video muted autoplay loop class="tv__player">
                <source src="/splashscreen/videos/screen3.mp4" />
            </video>
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
</html>
