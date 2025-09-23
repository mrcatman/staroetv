let body = $('body');
import replaceDom from './replaceDom';

$(body).on('click', '.programs-list__show-all .button', function() {
    const programsList = $(this).parents('.programs-list');
    $(programsList).append(' <div class="form__preloader"><img src="/img/ajax.gif"></div>');
    let url = $(this).data('is-radio') ? '/radio/programs/ajax' : '/video/programs/ajax';
    if ($(this).data('category')) {
        url= `${url}?category=${$(this).data('category')}`;
    }
    $.get(url).then(res => {
        $(programsList).removeClass('programs-list--with-show-all');
        $(programsList).find('.form__preloader').remove();
        if (res.status) {
            replaceDom(res.data.dom);
        }
    })
});
