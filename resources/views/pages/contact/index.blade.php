@extends('layouts.default')
@section('content')
    <form class="form box form--with-captcha" method="POST">
        <div class="box__heading">
            Обратная связь
        </div>
        <div class="box__inner">
            <div class="response"></div>
            <div class="contact-form">
                <div class="contact-form__content">
                    <div class="input-container">
                        <label class="input-container__label">Ваше имя<span class="input-container__required">*</span></label>
                        <div class="input-container__inner">
                            <input class="input" name="name" />
                            <span class="input-container__message"></span>
                        </div>
                    </div>
                    <div class="input-container">
                        <label class="input-container__label">Контакт для связи<span class="input-container__required">*</span></label>
                        <div class="input-container__inner">
                            <input class="input" name="contact" />
                            <span class="input-container__message"></span>
                        </div>
                    </div>
                    <div class="input-container">
                        <label class="input-container__label">Ваше сообщение<span class="input-container__required">*</span></label>
                        <div class="input-container__inner">
                            <textarea class="input" name="text"></textarea>
                            <span class="input-container__message"></span>
                        </div>
                    </div>
                    <button class="button">Отправить</button>

                </div>
                <div class="contact-form__description">
                    <div class="contact-form__description__title"> Для чего нужна форма связи с администрацией?</div>
                    <div class="contact-form__description__text">
                        - Предложение сотрудничества; <br>
                        - Предложение текстовых материалов для публикации; <br>
                        - Сообщение о нарушении авторских прав; <br>
                        - Сообщение об ошибках в работе сайта; <br>
                        - Сообщение о любой технической проблеме в использовании сайта; <br>
                        - Идеи улучшения дизайна, функционала и контента сайта; <br>
                    </div>
                </div>
            </div>
        </div>
        @csrf
    </form>

@endsection
