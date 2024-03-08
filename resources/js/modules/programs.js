let body = $('body');
import replaceDom from './replaceDom';

$(body).on('click', '.programs-list__show-all .button', function() {
    $(this).parents('.programs-list').append(' <div class="form__preloader"><img src="/pictures/ajax.gif"></div>');
    let url = $(this).data('is-radio') ? '/radio/programs/ajax' : '/video/programs/ajax';
    if ($(this).data('category')) {
        url+= '?category='+ $(this).data('category');
    }
    $.get(url).then(res => {
        $(this).parents('.programs-list').find('.form__preloader').remove();
        if (res.status) {
            replaceDom(res.data.dom);
        }
    })
});
