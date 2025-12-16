import replaceDom from './replaceDom';

const body = $('body');
const notifications = $('.notifications');

let page = 1;
$(body).on('click', '.auth-panel__button--notifications, .mobile-menu__item--notifications', function () {
    $('.auth-panel__button--notifications .auth-panel__button__count, .mobile-menu__item--notifications .mobile-menu__item__count').hide();
    $(notifications).show();
    $('.notifications__list').html('<div class="form__preloader"><img src="/img/ajax.gif"></div>');

    page = 1;

    $.get(route('profile.notifications')).done(res => {
        if (res.status) {
            replaceDom(res.data.dom);
        } else {
            alert(res.text);
        }
    })
});

$(document).click(function (event) {
    let $target = $(event.target);
    if (!$target.closest('.notifications').length && !$target.closest('.auth-panel__button--notifications').length && !$target.closest('.mobile-menu__item--notifications').length &&
        $(notifications).is(":visible")) {
        $(notifications).hide();
    }
});

$(body).on('click', '.notification', function() {
    $(notifications).hide();
})

$(body).on('click', '.notifications__more', function (e) {
    e.preventDefault();

    page++;
    $.get(`/profile/notifications?page=${page}`).done(res => {
        if (res.status) {
            replaceDom(res.data.dom);
            $(this).css('display', res.data.show_more ? '' : 'none');
        } else {
            alert(res.text);
        }
    })
})
