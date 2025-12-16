<div class="footer">
    <div class="footer__menu">
        <div class="container">
            <div class="footer__menu__inner">
                <a class="footer__menu__link" href="{{route('pages.show', 127)}}">О проекте</a>
                <a class="footer__menu__link" href="{{route('pages.team')}}">Команда сайта</a>
                <a class="footer__menu__link" href="{{route('pages.show', 141)}}">Помочь сайту</a>
                <a class="footer__menu__link" href="{{route('pages.show', 133)}}">Правила</a>
                <a class="footer__menu__link" href="{{route('contact.index')}}">Обратная связь</a>
                <a class="footer__menu__link" href="{{route('users.index')}}">Пользователи</a>
                <a class="footer__menu__link" href="{{route('top-list.videos')}}">Топ пользователей</a>
                <div class="footer__social">
                    @include('blocks.global.social')
                </div>
            </div>
        </div>
    </div>
    <div class="footer__copyright">
        <div class="container">
            <div class="footer__copyright__inner">
                <div class="footer__copyright__text">
                    Дизайн и верстка сайта © «Старый телевизор»; 2008 - {{date('Y')}}
                    Все аудио- и видеоматериалы, размещённые на сайте, принадлежат их владельцам. Нахождение материалов на сайте не оспаривает авторские права их создателей.
                </div>
                <a class="footer__light-switch"></a>
           </div>
        </div>
    </div>
</div>
