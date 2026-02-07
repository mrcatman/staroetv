let body = $('body');
import { replaceHTML} from './replace-html';
import { FORM_PRELOADER_CLASS, FORM_PRELOADER_HTML } from "./preloader";

$(body).on('click', '.programs-list__show-all .button', function() {
    const programsList = $(this).parents('.programs-list__container').find('.programs-list');
    $(programsList).append(FORM_PRELOADER_HTML);
    const params = {};
    if ($(this).data('category')) {
        params.category = $(this).data('category');
    }
    if ($(this).data('period')) {
        params.period = $(this).data('period');
    }

    $.get(route(`records.${$(this).data('is-radio')  ? 'radio' : 'video'}.programs.show-all`, params)).then(res => {
        $(programsList).removeClass('programs-list--with-show-all');
        $(programsList).find(`.${FORM_PRELOADER_CLASS}`).remove();
        if (res.status) {
            replaceHTML(res.data.html);
        }
    })
});
