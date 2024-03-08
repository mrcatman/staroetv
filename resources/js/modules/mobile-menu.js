
let body = $('body');
$(body).on('click', '.auth-panel__button--menu', function() {
    $('.mobile-menu').toggle();
    $(this).toggleClass('auth-panel__button--menu--active');
});
$(body).on('click', '.mobile-menu__item', function() {
    if (!$(this).hasClass('mobile-menu__item--notifications')) {
        $('.mobile-menu').hide();
    }
});
