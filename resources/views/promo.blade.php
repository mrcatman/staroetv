<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>ТЕЛЕпорт "Старого Телевизора"</title>
    @vite([ 'resources/sass/promo/index.scss'])
</head>
<body>
<div class="main">
    <div class="loader" id="loader"
         style="position: absolute;top:0;left:0;width: 100%;height: 100%;z-index:100000000;background:#111;color: #fff;display: flex;flex-direction:column;align-items:center;justify-content:center">
        <div class="loader__tv">
            <div class="loader__tv__antenna"></div>
            <div class="loader__tv__screen">
                <div class="loader__tv__screen__outer">
                    <div class="loader__tv__screen__inner" id="loader_line">
                        <div class="loader__tv__screen__line"></div>
                        <div class="loader__tv__screen__line"></div>
                        <div class="loader__tv__screen__line"></div>
                        <div class="loader__tv__screen__line"></div>
                        <div class="loader__tv__screen__line"></div>
                        <div class="loader__tv__screen__line"></div>
                        <div class="loader__tv__screen__line"></div>
                        <div class="loader__tv__screen__line"></div>
                        <div class="loader__tv__screen__line"></div>
                        <div class="loader__tv__screen__line"></div>
                        <div class="loader__tv__screen__line"></div>
                        <div class="loader__tv__screen__line"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="loader__percent" id="loader_percent">0%</div>
    </div>

    <div class="remote__container">
        <div class="remote">
            <div class="remote__logo">
                @include ('blocks.global.logo')
            </div>
            <div class="remote__inner">
                <div class="remote__random">Случайный канал</div>
                <div class="remote__channels" id="channels">

                </div>
            </div>
        </div>
    </div>


    <div class="tv">
        <div class="tv__picture"></div>
        <div class="tv__outer">
            <div class="tv__inner">
                <div class="tv__overlay" id="overlay">
                    <div class="tv__overlay__number" id="channel_number"></div>
                    <div class="tv__overlay__name" id="channel_name"></div>
                </div>

                <div class="tv__noise" id="noise"></div>
                <div id="player" class="tv__player"></div>
            </div>
        </div>
        <div class="tv__record-title" id="record_title"></div>

        <div class="tv__controls">
            <div class="tv__controls__group">
                <div class="tv__control" id="control_next_channel">
                    <svg class="tv__control__icon" viewBox="0 0 30.727 30.727"
                         xml:space="preserve">
                                <g>
                                    <path d="M29.994,10.183L15.363,24.812L0.733,10.184c-0.977-0.978-0.977-2.561,0-3.536c0.977-0.977,2.559-0.976,3.536,0
                                		l11.095,11.093L26.461,6.647c0.977-0.976,2.559-0.976,3.535,0C30.971,7.624,30.971,9.206,29.994,10.183z"/>
                                </g>
                                </svg>
                    <span class="tooltip">Следующий канал</span>
                </div>
                <div class="tv__control" id="control_prev_channel">
                    <svg class="tv__control__icon tv__control__icon--flip" viewBox="0 0 30.727 30.727"
                         xml:space="preserve">
                                <g>
                                    <path d="M29.994,10.183L15.363,24.812L0.733,10.184c-0.977-0.978-0.977-2.561,0-3.536c0.977-0.977,2.559-0.976,3.536,0
                                		l11.095,11.093L26.461,6.647c0.977-0.976,2.559-0.976,3.535,0C30.971,7.624,30.971,9.206,29.994,10.183z"/>
                                </g>
                                </svg>
                    <span class="tooltip">Предыдущий канал</span>
                </div>
                <div class="tv__control" id="control_refresh">
                    <svg class="tv__control__icon" viewBox="0 0 24 24">
                        <path
                            d="M10 11H7.101l.001-.009a4.956 4.956 0 0 1 .752-1.787 5.054 5.054 0 0 1 2.2-1.811c.302-.128.617-.226.938-.291a5.078 5.078 0 0 1 2.018 0 4.978 4.978 0 0 1 2.525 1.361l1.416-1.412a7.036 7.036 0 0 0-2.224-1.501 6.921 6.921 0 0 0-1.315-.408 7.079 7.079 0 0 0-2.819 0 6.94 6.94 0 0 0-1.316.409 7.04 7.04 0 0 0-3.08 2.534 6.978 6.978 0 0 0-1.054 2.505c-.028.135-.043.273-.063.41H2l4 4 4-4zm4 2h2.899l-.001.008a4.976 4.976 0 0 1-2.103 3.138 4.943 4.943 0 0 1-1.787.752 5.073 5.073 0 0 1-2.017 0 4.956 4.956 0 0 1-1.787-.752 5.072 5.072 0 0 1-.74-.61L7.05 16.95a7.032 7.032 0 0 0 2.225 1.5c.424.18.867.317 1.315.408a7.07 7.07 0 0 0 2.818 0 7.031 7.031 0 0 0 4.395-2.945 6.974 6.974 0 0 0 1.053-2.503c.027-.135.043-.273.063-.41H22l-4-4-4 4z"/>
                    </svg>
                    <span class="tooltip">Обновить канал</span>
                </div>
            </div>

            <div class="tv__controls__group">
                <div class="tv__controls__year" id="record_year"></div>
            </div>
            <div class="tv__controls__group">
                <div class="tv__control" id="control_next_channel">
                    <svg class="tv__control__icon"viewBox="0 0 416.979 416.979"
                         xml:space="preserve">
<g>
    <path d="M356.004,61.156c-81.37-81.47-213.377-81.551-294.848-0.182c-81.47,81.371-81.552,213.379-0.181,294.85
		c81.369,81.47,213.378,81.551,294.849,0.181C437.293,274.636,437.375,142.626,356.004,61.156z M237.6,340.786
		c0,3.217-2.607,5.822-5.822,5.822h-46.576c-3.215,0-5.822-2.605-5.822-5.822V167.885c0-3.217,2.607-5.822,5.822-5.822h46.576
		c3.215,0,5.822,2.604,5.822,5.822V340.786z M208.49,137.901c-18.618,0-33.766-15.146-33.766-33.765
		c0-18.617,15.147-33.766,33.766-33.766c18.619,0,33.766,15.148,33.766,33.766C242.256,122.755,227.107,137.901,208.49,137.901z"/>
</g>
</svg>
                    <span class="tooltip">О проекте</span>
                </div>
            </div>
        </div>
    </div>
    <div class="tapes" id="tapes"></div>
    <button class="reload-programs" id="reload_programs">Поменять передачи</button>

    <div class="about" style="display: none">
        <div class="about__content">
            <p class="about__text">
                Насладитесь телевидением прошлых лет (с советской эпохи и до 2010 года) с нашим ТЕЛЕпортом. Просто выберите канал и год - или доверьтесь случайности.
            </p>
            <h2 class="about__heading">Доступность видео</h2>
            <p class="about__text">
                К сожалению, партия сочла некоторые источники наших архивов вражескими. Вы и так знаете, что использовать для доступа к ним :)<br/>
                Если не хотите видеть современную богомерзкую рекламу, то можно использовать любой блокировщик.
            </p>
            <h2 class="about__heading">Есть что предложить?</h2>
            <p class="about__text">
                Если у вас всё ещё хранятся кассеты или любой другой носитель с записями передач, которых нет на сайте, то вы можете <a target="_blank" href="/tape-digitization">помочь архиву</a>.
            </p>
            <h2>О проекте</h2>
            <p class="about__text">
                Разработано <a target="_blank" href="https://mrcatmann.ru">mrcatmann</a> и командой сайта <a target="_blank" href="https://staroetv.su">"Старый телевизор"</a>.
                За основу взята идея проекта <a target="_blank" href="http://myretrotvs.com/">MyRetroTVs</a>.
            </p>
            <a class="about__close">Закрыть</a>
        </div>
    </div>
</div>
</body>
@routes
@vite(['resources/js/promo/index.ts'])
</html>
