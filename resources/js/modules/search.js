let body = $('body');
import replaceDom from './replaceDom';

let searchInputTimeout = null;

$(body).on('click', '.auth-panel__button--search, .mobile-menu__item--search', function() {
    $('.site-search').show();
    setTimeout(() => {
        $('.site-search__input').focus();
    }, 50)
});

$(body).on('click', '.site-search__close', function() {
    $('.site-search').hide();
});
$(body).on('keyup', '.site-search__input', function(e) {
    if (e.keyCode === 27) {
        $('.site-search').hide();
        return;
    }
    if ($(this).val().length > 0) {
        clearTimeout(searchInputTimeout);
        searchInputTimeout = setTimeout(() => {
            if ($(this).val().length >= 3) {
                $('.site-search__results').html('<div class="block-preloader"><img src="/pictures/ajax.gif"></div>');
                $.get('/site-search?search=' + $(this).val()).done(res => {
                    replaceDom(res.data.dom);
                })
            }
        }, 500)
    }
});
