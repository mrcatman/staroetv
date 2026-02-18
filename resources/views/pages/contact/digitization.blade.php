@extends('layouts.default')
@section('content')
    <div class="box">
        <div class="box__heading">
            <h1 class="box__heading__inner">
                Оцифровка кассет
            </h1>
        </div>
        <div class="box__inner">
            <div class="text-content digitization">
                <div class="digitization__main">
                    Если у вас есть записи старых телепрограмм или даже рекламных роликов, которых ещё нет в Сети, и вы
                    хотите их оцифровать, чтобы добавить видео на сайт,
                    воспользуйтесь услугами добровольцев-оцифровщиков, проживающих в России и странах СНГ.
                </div>
                <div class="horisontal-delimiter"></div>
                <h2>Какие носители мы принимаем</h2>
                <div class="row">
                    <div class="col">
                        <div class="digitization__format">
                            <img class="digitization__format__picture"
                                 src="{{Vite::asset('resources/images/digitization/vhs.webp') }}"
                                 alt="Видеокассеты VHS"/>
                            <div class="digitization__format__name">Видеокассеты VHS</div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="digitization__format">
                            <img class="digitization__format__picture"
                                 src="{{Vite::asset('resources/images/digitization/super-vhs.webp') }}"
                                 alt="Видеокассеты S-VHS (Super VHS)"/>
                            <div class="digitization__format__name">Видеокассеты S-VHS (Super VHS)</div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="digitization__format">
                            <img class="digitization__format__picture"
                                 src="{{Vite::asset('resources/images/digitization/betacam-sp.webp') }}"
                                 alt="Видеокассеты Betacam SP"/>
                            <div class="digitization__format__name">Видеокассеты Betacam SP</div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="digitization__format">
                            <img class="digitization__format__picture"
                                 src="{{Vite::asset('resources/images/digitization/audio.webp') }}" alt="Аудиокассеты"/>
                            <div class="digitization__format__name">Аудиокассеты</div>
                        </div>
                    </div>
                </div>

                <div class="horisontal-delimiter"></div>
                <h2>Оставить заявку на оцифровку</h2>
            </div>
            <br/>
            <form class="form form--with-captcha" data-reset="1" method="POST">
                @csrf
                <div class="form__content">
                    <div class="response"></div>
                    <div class="input-container">
                        <label class="input-container__label">Ваше имя<span
                                class="input-container__required">*</span></label>
                        <div class="input-container__inner">
                            <input class="input" name="name" value="{{auth()->user() ? auth()->user()->name : ''}}"/>
                            <span class="input-container__message"></span>
                        </div>
                    </div>
                    <div class="input-container">
                        <label class="input-container__label">Город<span
                                class="input-container__required">*</span></label>
                        <div class="input-container__inner">
                            <input class="input" name="city"/>
                        </div>
                        <span class="input-container__message"></span>
                    </div>
                    <div class="input-container">
                        <label class="input-container__label">Контакты<span
                                class="input-container__required">*</span></label>
                        <div class="input-container__inner">
                            <div class="input-container__element-outer">
                                <input class="input" name="contact"
                                       value="{{auth()->user() ? auth()->user()->email : ''}}"/>
                                <span class="input-container__description">Telegram, почта, ВК</span>
                            </div>
                            <span class="input-container__message"></span>
                        </div>
                    </div>

                    <div class="input-container">
                        <label class="input-container__label">Описание<span
                                class="input-container__required">*</span></label>
                        <div class="input-container__inner">
                            <div class="input-container__element-outer">
                                <textarea class="input" name="text"></textarea>
                                <span
                                    class="input-container__description">Примерное количество и содержимое кассет</span>

                            </div>
                            <span class="input-container__message"></span>
                        </div>
                    </div>
                    <div class="form__bottom">
                        <button class="button">Отправить</button>
                        <a href="https://t.me/staroetv?direct" target="_blank">
                            либо написать нам через Телеграм
                        </a>
                    </div>
                    <br/>
                </div>
            </form>

            <div class="horisontal-delimiter"></div>
            <div class="text-content digitization">
                <br/>
                <h2>Вопросы и ответы</h2>
                <div class="faq">
                    <div class="faq__question">
                        Как осуществляется пересылка материала?
                    </div>
                    <div class="faq__answer">
                        Любой службой доставки (Яндекс, СДЭК, Почта) за счёт самого сайта.
                    </div>
                </div>
                <div class="faq">
                    <div class="faq__question">
                        Возможно ли получить кассеты обратно после оцифровки?
                    </div>
                    <div class="faq__answer">
                        Да, конечно.
                    </div>
                </div>
                <div class="faq">
                    <div class="faq__question">
                        Сколько времени займёт оцифровка?
                    </div>
                    <div class="faq__answer">
                        Зависит от количества носителей и загруженности наших волонтёров.
                    </div>
                </div>
                <div class="faq">
                    <div class="faq__question">
                        Представляет ли ценность такая-то передача?
                    </div>
                    <div class="faq__answer">
                        Уточните через форму или в телеграм-канале.
                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection
