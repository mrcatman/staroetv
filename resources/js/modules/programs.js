let body = $('body');
import { replaceHTML} from './replace-html';
import { FORM_PRELOADER_CLASS, FORM_PRELOADER_HTML } from "./preloader";

$(body).on('click', '.programs-list__show-more .button', function() {
    const showMoreContainer = $(this).parents('.programs-list__show-more');
    $(showMoreContainer).append(FORM_PRELOADER_HTML);
    const params = {};
    if ($(this).data('category')) {
        params.category = $(this).data('category');
    }
    if ($(this).data('period')) {
        params.period = $(this).data('period');
    }
    if ($(this).data('channel-id')) {
        params.channel_id = $(this).data('channel-id');
    }
    if ($(this).data('limit')) {
        params.limit = $(this).data('limit');
    }

    params.page = ($(this).data('page') || 2);
    $.get(route(`records.${$(this).data('is-radio')  ? 'radio' : 'video'}.programs.show-more`, params)).then(res => {
        $(showMoreContainer).find(`.${FORM_PRELOADER_CLASS}`).remove();
        if (res.status) {
            replaceHTML(res.data.html);
            $(this).data('page', params.page + 1);
        }
    })
});
