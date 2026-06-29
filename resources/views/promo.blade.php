<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>ТЕЛЕпорт "Старого Телевизора"</title>

    <link rel="preload" href="https://vk.com/js/api/videoplayer.js" as="script"/>
    <link rel="preload" href="{{Vite::asset('resources/fonts/promo/Electronica-Normal.ttf')}}" as="font"
          crossorigin="anonymous"/>
    <link rel="preload" href="{{Vite::asset('resources/fonts/promo/Electronica-Normal.woff2')}}" as="font"
          type="font/woff2" crossorigin="anonymous">
    <link rel="preload" href="{{Vite::asset('resources/images/promo/background.webp')}}" as="image"/>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@100..900&display=swap" rel="stylesheet">

    @vite([ 'resources/sass/promo/index.scss'])
</head>
<body>
<div class="main" id="main">
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

    <div class="close-remote__container" id="close_remote_container" style="display: none">
        <div class="button close-remote" id="close_remote">Убрать пульт</div>

    </div>
    <div class="remote__container">
        <div class="remote" id="remote">
            <div class="remote__outer">
                <div class="remote__logo">
                    @include ('blocks.global.logo')
                </div>
                <div id="remote_main" class="remote__inner">
                    <button class="remote__button remote__button--random" id="remote_random">Случайный канал</button>
                    <button class="remote__button remote__button--commercials" id="remote_commercials">Рекламные
                        ролики
                    </button>
                    <div class="remote__channels" id="remote_channels"></div>

                    <button class="remote__button" id="remote_show_all">Все каналы</button>
                </div>
                <div id="remote_all" class="remote__inner" style="display: none">
                    <button class="remote__button remote__button--back" id="remote_back">Назад</button>
                    <div class="remote__all-channels" id="remote_all_channels"></div>
                </div>
            </div>
        </div>
    </div>


    <div class="tv">
        <div class="tv__picture"></div>
        <div class="tv__outer">
            <div class="tv__inner" id="inner">
                <div class="tv__overlay" id="overlay">
                    <div class="tv__overlay__number" id="channel_number"></div>
                    <div class="tv__overlay__name" id="channel_name"></div>
                </div>

                <div class="tv__not-found" id="not_found" style="display:none">
                    <div class="tv__not-found__text">
                        <div class="tv__not-found__text__title">К сожалению, по вашим параметрам не нашлось записей :(
                        </div>
                        <div class="tv__not-found__text__description">Попробуйте изменить канал, год или жанр</div>
                    </div>
                </div>
                <div class="tv__flash" id="flash" style="opacity: 0"></div>
                <video playsinline autoplay muted loop class="tv__noise" id="noise" style="opacity: 0">
                    <source src="{{Vite::asset('resources/images/promo/noise.webm')}}" type="video/webm">
                </video>
                <video playsinline autoplay muted loop class="tv__intro" id="intro">
                    <source src="{{Vite::asset('resources/images/promo/intro.mp4')}}" type="video/mp4">
                </video>
                <div id="player" class="tv__player"></div>
            </div>
        </div>
        <div class="tv__record-title" id="record_title"></div>

        <div class="tv__controls">
            <div class="tv__controls__group tv__controls__group--desktop-only">
                <div class="tv__control" id="control_prev_channel">
                    <svg class="tv__control__icon" viewBox="-5.5 0 26 26">
                        <g fill-rule="evenodd" >
                            <g  transform="translate(-423.000000, -1196.000000)">
                                <path d="M428.115,1209 L437.371,1200.6 C438.202,1199.77 438.202,1198.43 437.371,1197.6 C436.541,1196.76 435.194,1196.76 434.363,1197.6 L423.596,1207.36 C423.146,1207.81 422.948,1208.41 422.985,1209 C422.948,1209.59 423.146,1210.19 423.596,1210.64 L434.363,1220.4 C435.194,1221.24 436.541,1221.24 437.371,1220.4 C438.202,1219.57 438.202,1218.23 437.371,1217.4 L428.115,1209" id="chevron-left" sketch:type="MSShapeGroup"></path>
                            </g>
                        </g>
                    </svg>
                    <span class="tooltip">Предыдущий канал</span>
                </div>
                <div class="tv__control" id="control_next_channel">
                    <svg class="tv__control__icon" viewBox="-5.5 0 26 26">
                        <g  fill-rule="evenodd">
                            <g  transform="translate(-474.000000, -1196.000000)" >
                                <path d="M488.404,1207.36 L477.637,1197.6 C476.806,1196.76 475.459,1196.76 474.629,1197.6 C473.798,1198.43 473.798,1199.77 474.629,1200.6 L483.885,1209 L474.629,1217.4 C473.798,1218.23 473.798,1219.57 474.629,1220.4 C475.459,1221.24 476.806,1221.24 477.637,1220.4 L488.404,1210.64 C488.854,1210.19 489.052,1209.59 489.015,1209 C489.052,1208.41 488.854,1207.81 488.404,1207.36" id="chevron-right" sketch:type="MSShapeGroup"></path>
                            </g>
                        </g>
                    </svg>
                    <span class="tooltip">Следующий канал</span>
                </div>

                <!--
                <div class="tv__control" id="control_refresh">
                    <svg class="tv__control__icon" viewBox="0 0 24 24">
                        <path
                            d="M10 11H7.101l.001-.009a4.956 4.956 0 0 1 .752-1.787 5.054 5.054 0 0 1 2.2-1.811c.302-.128.617-.226.938-.291a5.078 5.078 0 0 1 2.018 0 4.978 4.978 0 0 1 2.525 1.361l1.416-1.412a7.036 7.036 0 0 0-2.224-1.501 6.921 6.921 0 0 0-1.315-.408 7.079 7.079 0 0 0-2.819 0 6.94 6.94 0 0 0-1.316.409 7.04 7.04 0 0 0-3.08 2.534 6.978 6.978 0 0 0-1.054 2.505c-.028.135-.043.273-.063.41H2l4 4 4-4zm4 2h2.899l-.001.008a4.976 4.976 0 0 1-2.103 3.138 4.943 4.943 0 0 1-1.787.752 5.073 5.073 0 0 1-2.017 0 4.956 4.956 0 0 1-1.787-.752 5.072 5.072 0 0 1-.74-.61L7.05 16.95a7.032 7.032 0 0 0 2.225 1.5c.424.18.867.317 1.315.408a7.07 7.07 0 0 0 2.818 0 7.031 7.031 0 0 0 4.395-2.945 6.974 6.974 0 0 0 1.053-2.503c.027-.135.043-.273.063-.41H22l-4-4-4 4z"/>
                    </svg>
                    <span class="tooltip">Другая запись</span>
                </div>
                -->
            </div>


            <div class="tv__category">
                <div class="tv__category__label">Год</div>
                <button class="tv__category__value tv__category__value--years" id="control_year">любой</button>
                <div style="display: none" class="tv__category__list tv__category__list--years"
                     id="control_year_list"></div>
            </div>

            <div class="tv__category">
                <div class="tv__category__label">Жанр</div>
                <button class="tv__category__value tv__category__value--genres" id="control_genre">любой</button>
                <div style="display: none" class="tv__category__list tv__category__list--genres"
                     id="control_genre_list"></div>
            </div>

            <div class="tv__controls__group">
                <a class="tv__control tv__control--disabled" id="control_go_to_record">
                    <svg class="tv__control__icon tv__control__icon--small" viewBox="0 0 16 16">
                        <path d="M14 3.5L8.5 9 7 7.5 12.5 2H10V0h6v6h-2V3.5zM6 0v2H2v12h12v-4h2v6H0V0h6z"
                              fill-rule="evenodd"/>
                    </svg>
                    <span class="tooltip">Посмотреть видео полностью</span>
                </a>
                <div class="tv__control" id="control_about">
                    <svg class="tv__control__icon" viewBox="0 0 416.979 416.979"
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

            <div class="tv__on-off">
                <div class="tv__control" id="control_on_off">
                    <svg class="tv__control__icon" viewBox="0 0 512 512">
                        <path
                            d="M228.576 26.213v207.32h54.848V26.214h-54.848zm-28.518 45.744C108.44 96.58 41 180.215 41 279.605c0 118.74 96.258 215 215 215 118.74 0 215-96.26 215-215 0-99.39-67.44-183.025-159.057-207.647v50.47c64.6 22.994 110.85 84.684 110.85 157.177 0 92.117-74.676 166.794-166.793 166.794-92.118 0-166.794-74.678-166.794-166.795 0-72.494 46.25-134.183 110.852-157.178v-50.47z"/>
                    </svg>
                    <span class="tooltip">Вкл/выкл</span>
                </div>
            </div>

            <div class="tv__volume">
                <div class="tv__volume__outer">
                    <div class="tv__volume__label">Громкость</div>
                    <div class="tv__volume__inner" id="control_volume">
                        <div class="tv__volume__indicator"></div>
                    </div>
                    <div class="tv__volume__text tv__volume__text--min">0%</div>
                    <div class="tv__volume__text tv__volume__text--max">100%</div>
                </div>
            </div>
        </div>
    </div>
    <div class="tapes" id="tapes"></div>
    <button class="button reload-programs" id="reload_programs">
        <svg class="button__icon" viewBox="0 0 24 24">
            <path
                d="M10 11H7.101l.001-.009a4.956 4.956 0 0 1 .752-1.787 5.054 5.054 0 0 1 2.2-1.811c.302-.128.617-.226.938-.291a5.078 5.078 0 0 1 2.018 0 4.978 4.978 0 0 1 2.525 1.361l1.416-1.412a7.036 7.036 0 0 0-2.224-1.501 6.921 6.921 0 0 0-1.315-.408 7.079 7.079 0 0 0-2.819 0 6.94 6.94 0 0 0-1.316.409 7.04 7.04 0 0 0-3.08 2.534 6.978 6.978 0 0 0-1.054 2.505c-.028.135-.043.273-.063.41H2l4 4 4-4zm4 2h2.899l-.001.008a4.976 4.976 0 0 1-2.103 3.138 4.943 4.943 0 0 1-1.787.752 5.073 5.073 0 0 1-2.017 0 4.956 4.956 0 0 1-1.787-.752 5.072 5.072 0 0 1-.74-.61L7.05 16.95a7.032 7.032 0 0 0 2.225 1.5c.424.18.867.317 1.315.408a7.07 7.07 0 0 0 2.818 0 7.031 7.031 0 0 0 4.395-2.945 6.974 6.974 0 0 0 1.053-2.503c.027-.135.043-.273.063-.41H22l-4-4-4 4z"/>
        </svg>
        Поменять кассеты
    </button>
    <button class="button programs-back" id="programs_back">
        <svg viewBox="0 0 1024 1024" class="button__icon">
            <path d="M224 480h640a32 32 0 1 1 0 64H224a32 32 0 0 1 0-64z"/>
            <path
                d="m237.248 512 265.408 265.344a32 32 0 0 1-45.312 45.312l-288-288a32 32 0 0 1 0-45.312l288-288a32 32 0 1 1 45.312 45.312L237.248 512z"/>
        </svg>
        Назад
    </button>

    <div class="mobile-controls">
        <a class="mobile-controls__button mobile-controls__change-channel mobile-controls__change-channel--prev"
           id="mobile_control_prev_channel">
            <svg class="mobile-controls__icon" viewBox="-5.5 0 26 26">
                <g fill-rule="evenodd" >
                    <g  transform="translate(-423.000000, -1196.000000)">
                        <path d="M428.115,1209 L437.371,1200.6 C438.202,1199.77 438.202,1198.43 437.371,1197.6 C436.541,1196.76 435.194,1196.76 434.363,1197.6 L423.596,1207.36 C423.146,1207.81 422.948,1208.41 422.985,1209 C422.948,1209.59 423.146,1210.19 423.596,1210.64 L434.363,1220.4 C435.194,1221.24 436.541,1221.24 437.371,1220.4 C438.202,1219.57 438.202,1218.23 437.371,1217.4 L428.115,1209" id="chevron-left" sketch:type="MSShapeGroup"></path>
                    </g>
                </g>
            </svg>
            <div class="mobile-controls__label">Пред. канал</div>
        </a>
        <a class="mobile-controls__button mobile-controls__change-channel mobile-controls__change-channel--next"
           id="mobile_control_next_channel">
            <svg class="mobile-controls__icon" viewBox="-5.5 0 26 26">
                 <g  fill-rule="evenodd">
                    <g  transform="translate(-474.000000, -1196.000000)" >
                        <path d="M488.404,1207.36 L477.637,1197.6 C476.806,1196.76 475.459,1196.76 474.629,1197.6 C473.798,1198.43 473.798,1199.77 474.629,1200.6 L483.885,1209 L474.629,1217.4 C473.798,1218.23 473.798,1219.57 474.629,1220.4 C475.459,1221.24 476.806,1221.24 477.637,1220.4 L488.404,1210.64 C488.854,1210.19 489.052,1209.59 489.015,1209 C489.052,1208.41 488.854,1207.81 488.404,1207.36" id="chevron-right" sketch:type="MSShapeGroup"></path>
                    </g>
                </g>
            </svg>
            <div class="mobile-controls__label">След. канал</div>
        </a>
        <a class="mobile-controls__button mobile-controls__button--center mobile-controls__toggle-remote" id="toggle_remote">
            <svg class="mobile-controls__icon" viewBox="0 -0.5 21 21">
                <g stroke="none" stroke-width="1" fill-rule="evenodd">
                    <g transform="translate(-99.000000, -200.000000)">
                        <g transform="translate(56.000000, 160.000000)">
                            <path
                                d="M60.85,51 L57.7,51 C55.96015,51 54.55,52.343 54.55,54 L54.55,57 C54.55,58.657 55.96015,60 57.7,60 L60.85,60 C62.58985,60 64,58.657 64,57 L64,54 C64,52.343 62.58985,51 60.85,51 M49.3,51 L46.15,51 C44.41015,51 43,52.343 43,54 L43,57 C43,58.657 44.41015,60 46.15,60 L49.3,60 C51.03985,60 52.45,58.657 52.45,57 L52.45,54 C52.45,52.343 51.03985,51 49.3,51 M60.85,40 L57.7,40 C55.96015,40 54.55,41.343 54.55,43 L54.55,46 C54.55,47.657 55.96015,49 57.7,49 L60.85,49 C62.58985,49 64,47.657 64,46 L64,43 C64,41.343 62.58985,40 60.85,40 M52.45,43 L52.45,46 C52.45,47.657 51.03985,49 49.3,49 L46.15,49 C44.41015,49 43,47.657 43,46 L43,43 C43,41.343 44.41015,40 46.15,40 L49.3,40 C51.03985,40 52.45,41.343 52.45,43"
                                id="menu_navigation_grid-[#1529]"></path>
                        </g>
                    </g>
                </g>
            </svg>
            <div class="mobile-controls__label">Показать пульт</div>
        </a>
        <a class="mobile-controls__button mobile-controls__button--center mobile-controls__random-channel" id="random_channel">
            <svg class="mobile-controls__icon" viewBox="50 50 430 430" >
                <g stroke-width="1" fill-rule="evenodd">
                    <g id="icon" transform="translate(46.976875, 46.976875)">
                        <path d="M379.689791,38.3564581 L379.689791,379.689791 L38.3564581,379.689791 L38.3564581,38.3564581 L379.689791,38.3564581 Z M283.689791,251.689791 C266.016679,251.689791 251.689791,266.016679 251.689791,283.689791 C251.689791,301.362903 266.016679,315.689791 283.689791,315.689791 C301.362903,315.689791 315.689791,301.362903 315.689791,283.689791 C315.689791,266.016679 301.362903,251.689791 283.689791,251.689791 Z M209.023125,177.023125 C191.350013,177.023125 177.023125,191.350013 177.023125,209.023125 C177.023125,226.696237 191.350013,241.023125 209.023125,241.023125 C226.696237,241.023125 241.023125,226.696237 241.023125,209.023125 C241.023125,191.350013 226.696237,177.023125 209.023125,177.023125 Z M134.356458,102.356458 C116.683346,102.356458 102.356458,116.683346 102.356458,134.356458 C102.356458,152.02957 116.683346,166.356458 134.356458,166.356458 C152.02957,166.356458 166.356458,152.02957 166.356458,134.356458 C166.356458,116.683346 152.02957,102.356458 134.356458,102.356458 Z" id="Combined-Shape" transform="translate(209.023125, 209.023125) rotate(-345.000000) translate(-209.023125, -209.023125) "></path>
                    </g>
                </g>
            </svg>
            <div class="mobile-controls__label">Случайный канал</div>
        </a>

        <!--
        <a class="mobile-controls__button mobile-controls__refresh" id="mobile_control_refresh">
            <svg class="mobile-controls__icon" viewBox="0 0 24 24">
                <path
                    d="M10 11H7.101l.001-.009a4.956 4.956 0 0 1 .752-1.787 5.054 5.054 0 0 1 2.2-1.811c.302-.128.617-.226.938-.291a5.078 5.078 0 0 1 2.018 0 4.978 4.978 0 0 1 2.525 1.361l1.416-1.412a7.036 7.036 0 0 0-2.224-1.501 6.921 6.921 0 0 0-1.315-.408 7.079 7.079 0 0 0-2.819 0 6.94 6.94 0 0 0-1.316.409 7.04 7.04 0 0 0-3.08 2.534 6.978 6.978 0 0 0-1.054 2.505c-.028.135-.043.273-.063.41H2l4 4 4-4zm4 2h2.899l-.001.008a4.976 4.976 0 0 1-2.103 3.138 4.943 4.943 0 0 1-1.787.752 5.073 5.073 0 0 1-2.017 0 4.956 4.956 0 0 1-1.787-.752 5.072 5.072 0 0 1-.74-.61L7.05 16.95a7.032 7.032 0 0 0 2.225 1.5c.424.18.867.317 1.315.408a7.07 7.07 0 0 0 2.818 0 7.031 7.031 0 0 0 4.395-2.945 6.974 6.974 0 0 0 1.053-2.503c.027-.135.043-.273.063-.41H22l-4-4-4 4z"/>
            </svg>
            <div class="mobile-controls__label">Другая запись</div>
        </a>
        -->

        <!--
        <button class="button toggle-remote" id="toggle_remote">
            <svg class="button__icon" viewBox="0 -0.5 21 21">
                <g id="Page-1" stroke="none" stroke-width="1" fill-rule="evenodd">
                    <g id="Dribbble-Light-Preview" transform="translate(-99.000000, -200.000000)">
                        <g id="icons" transform="translate(56.000000, 160.000000)">
                            <path
                                d="M60.85,51 L57.7,51 C55.96015,51 54.55,52.343 54.55,54 L54.55,57 C54.55,58.657 55.96015,60 57.7,60 L60.85,60 C62.58985,60 64,58.657 64,57 L64,54 C64,52.343 62.58985,51 60.85,51 M49.3,51 L46.15,51 C44.41015,51 43,52.343 43,54 L43,57 C43,58.657 44.41015,60 46.15,60 L49.3,60 C51.03985,60 52.45,58.657 52.45,57 L52.45,54 C52.45,52.343 51.03985,51 49.3,51 M60.85,40 L57.7,40 C55.96015,40 54.55,41.343 54.55,43 L54.55,46 C54.55,47.657 55.96015,49 57.7,49 L60.85,49 C62.58985,49 64,47.657 64,46 L64,43 C64,41.343 62.58985,40 60.85,40 M52.45,43 L52.45,46 C52.45,47.657 51.03985,49 49.3,49 L46.15,49 C44.41015,49 43,47.657 43,46 L43,43 C43,41.343 44.41015,40 46.15,40 L49.3,40 C51.03985,40 52.45,41.343 52.45,43"
                                id="menu_navigation_grid-[#1529]">

                            </path>
                        </g>
                    </g>
                </g>
            </svg>
            Показать пульт
        </button>
        -->
        <button class="button toggle-programs" id="toggle_programs">
            <svg class="button__icon" viewBox="0 0 512 512">
                <g>
                    <g>
                        <rect y="80.696" width="512" height="70.678"/>
                    </g>
                </g>
                <g>
                    <g>
                        <path d="M0,184.765v246.539h512V184.765H0z M133.565,338.365H77.913c-13.457-30.101-13.457-64.508,0-94.609h55.652V338.365z
			 M325.565,338.647h-139.13v-94.609h139.13V338.647z M434.087,338.365h-55.652v-94.609h55.652
			C447.544,273.858,447.544,308.264,434.087,338.365z"/>
                    </g>
                </g>
            </svg>
            Выбрать кассету
        </button>

    </div>
</div>

<div id="about" class="about" style="display: none">
    <div class="about__content">
        <div class="about__main">
            <h2 class="about__heading">ТЕЛЕпорт</h2>
            <p class="about__text">
                Добро пожаловать на наш скромный симулятор лампового аналогового телека 90-х и нулевых.
            </p>
            <h2 class="about__heading">Инструкции</h2>
            <ul class="about__text">
                <li>Нажмите на <strong>экран телевизора</strong>, чтобы поменять видео.</li>
                <li>Можно выбрать конкретный канал с помощью <strong>пульта</strong> (нажмите на канал ещё раз, чтобы включить другое видео с этого канала)</li>

                <li>Кнопки <span class="about__button"> <svg class="about__button__icon" viewBox="-5.5 0 26 26">
                        <g fill-rule="evenodd" >
                            <g  transform="translate(-423.000000, -1196.000000)">
                                <path d="M428.115,1209 L437.371,1200.6 C438.202,1199.77 438.202,1198.43 437.371,1197.6 C436.541,1196.76 435.194,1196.76 434.363,1197.6 L423.596,1207.36 C423.146,1207.81 422.948,1208.41 422.985,1209 C422.948,1209.59 423.146,1210.19 423.596,1210.64 L434.363,1220.4 C435.194,1221.24 436.541,1221.24 437.371,1220.4 C438.202,1219.57 438.202,1218.23 437.371,1217.4 L428.115,1209" id="chevron-left" sketch:type="MSShapeGroup"></path>
                            </g>
                        </g>
                    </svg></span>, <span class="about__button"><svg class="about__button__icon" viewBox="-5.5 0 26 26">
                        <g  fill-rule="evenodd">
                            <g  transform="translate(-474.000000, -1196.000000)" >
                                <path d="M488.404,1207.36 L477.637,1197.6 C476.806,1196.76 475.459,1196.76 474.629,1197.6 C473.798,1198.43 473.798,1199.77 474.629,1200.6 L483.885,1209 L474.629,1217.4 C473.798,1218.23 473.798,1219.57 474.629,1220.4 C475.459,1221.24 476.806,1221.24 477.637,1220.4 L488.404,1210.64 C488.854,1210.19 489.052,1209.59 489.015,1209 C489.052,1208.41 488.854,1207.81 488.404,1207.36" id="chevron-right" sketch:type="MSShapeGroup"></path>
                            </g>
                        </g>
                    </svg></span> — следующий/предыдущий
                    канал соответственно
                </li>
                <li>С помощью кнопок на ТВ можно отфильтровать ролики по <strong>году</strong> и <strong>жанру</strong>.</li>
                <li>Можно выбрать конкретную передачу с помощью <strong>полки с кассетами</strong> (список всегда случайный)</li>
                <li>Кнопка <span class="about__button"><svg class="about__button__icon" viewBox="0 0 16 16">
                        <path d="M14 3.5L8.5 9 7 7.5 12.5 2H10V0h6v6h-2V3.5zM6 0v2H2v12h12v-4h2v6H0V0h6z"
                              fill-rule="evenodd"/>
                    </svg>  </span> - перейти к оригинальному видео
                </li>
            </ul>
            <h2 class="about__heading">Доступность видео</h2>
            <p class="about__text">
                К сожалению, часть видео на сайте залита на "вражеские" источники (Ютуб). При использовании сами-знаете-чего доступный архив может значительно расшириться :)<br/>
                Если не хотите видеть современную богомерзкую рекламу, можете использовать любой блокировщик.
            </p>
            <h2 class="about__heading">Почему нет сериалов и мультиков?</h2>
            <p class="about__text">
                Увы, это считается полноценным пиратством, программы всё же находятся в "серой зоне". Возможно, в будущем ситуация изменится.
            </p>
            <h2 class="about__heading">Есть что предложить?</h2>
            <p class="about__text">
                По всем вопросам и предложениям пишите в <a target="_blank" href="https://staroetv.su/contact">форму</a> или <a target="_blank" href="https://t.me/staroetv?direct">напрямую в Телеграм</a>.
                <br/>
                Если у вас всё ещё хранятся VHS кассеты или любой другой носитель с записями, которых нет на
                сайте, то вы можете <a target="_blank" href="https://staroetv.su/tape-digitization">помочь архиву</a>.
            </p>

            <h2 class="about__heading">О проекте</h2>
            <p class="about__text">
                Разработано <a target="_blank" href="https://mrcatmann.ru">mrcatmann</a> и командой сайта <a target="_blank" href="https://staroetv.su">"Старый телевизор"</a>.
                За основу взята идея проекта <a target="_blank" href="http://myretrotvs.com/">MyRetroTVs</a>.
            </p>

        </div>
        <a class="about__close" id="about_close">Продолжить</a>
    </div>
</div>
</body>
@routes
@vite(['resources/js/promo/index.ts'])

<!-- Yandex.Metrika counter -->
<script type="text/javascript">
    (function(m,e,t,r,i,k,a){
        m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
        m[i].l=1*new Date();
        for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
        k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)
    })(window, document,'script','https://mc.yandex.ru/metrika/tag.js?id=110041560', 'ym');

    ym(110041560, 'init', {ssr:true, webvisor:true, clickmap:true, ecommerce:"dataLayer", referrer: document.referrer, url: location.href, accurateTrackBounce:true, trackLinks:true});
</script>
<noscript><div><img src="https://mc.yandex.ru/watch/110041560" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<!-- /Yandex.Metrika counter -->

</html>
