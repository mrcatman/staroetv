import replaceDom from './replaceDom';
let body = $('body');

$(body).on('click', '.auth-panel__button--notifications, .mobile-menu__item--notifications', function() {
    $('.auth-panel__button--notifications .auth-panel__button__count, .mobile-menu__item--notifications .mobile-menu__item__count').hide();
    $('.notifications').show();
    $('.notifications__list').html('<div class="form__preloader"><img src="/pictures/ajax.gif"></div>');
    $.get('/profile/notifications').done(res => {
        if (res.status) {
            replaceDom(res.data.dom);
        } else {
            alert(res.text);
        }
    })
});

$(document).click(function(event) {
    let $target = $(event.target);
    if(!$target.closest('.notifications').length && !$target.closest('.auth-panel__button--notifications').length && !$target.closest('.mobile-menu__item--notifications').length &&
        $('.notifications').is(":visible")) {
        $('.notifications').hide();
    }
});

