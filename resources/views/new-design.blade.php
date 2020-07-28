<html><head>
    <style>
        @import url('https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700&display=swap&subset=cyrillic');

        body {
            margin: 0;
            padding: 0;
            background: #111;
            color: #fff;
            font-size: 20px;
            font-family: "Source Sans Pro", sans-serif;
        }
        .feed-item {
            margin: 1em;
            background: #fff;
        }

        .feed-item__title {
            font-size: 1.25em;
            font-weight: bold;
        }

        body {
            background: #fcfcfc;
            color: #333;
        }

        .feed {
            max-width: 860px;
            margin: 0 auto;
        }

        .feed-item__cover {
            padding-top: 500px;
            background-size: cover;
            background-position: center center;
            background-repeat: no-repeat;
            height: 0;
        }

    </style>
</head>
<body>
<div class="header">
    <div class="header__left">
        <div class="logo">Старый Телевизор</div>
        <div class="top-menu">
            <a class="top-menu__link" href="/articles">Статьи</a>
            <a class="top-menu__link" href="/videos">Видеоархив</a>
            <a class="top-menu__link" href="/forum">Форум</a>
        </div>
    </div>
    <div class="header__right">
        <div class="profile">
            Добро пожаловать, <a href="/index/8-1">username</a>!
            <a class="profile__logout">Выйти</a>
        </div>
    </div>
</div>
<div class="main">
    <div class="container">
        <div class="feed">
            @foreach (\App\Article::orderBy('id', 'desc')->limit(10)->get() as $article)
                <div class="feed-item">
                    <div class="feed-item__cover" style="background-image:url({{$article->cover}})"></div>
                    <div class="feed-item__info">
                        <div class="feed-item__title">{{$article->title}}</div>
                        <div class="feed-item__short-description">{{$article->short_content}}</div>
                    </div>

                </div>
            @endforeach
        </div>
        <div class="videos-grid">
            @foreach (\App\Record::where(['is_radio' => false])->orderBy('id', 'desc')->limit(10)->get() as $video)
            <div class="video-item" style="background-image:url({{$video->cover}}">
                <div class="video-item__title">{{$video->title}}</div>
            </div>
            @endforeach
        </div>

    </div>

</div></body></html>
