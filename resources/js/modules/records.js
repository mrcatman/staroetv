import {PRELOADER_CLASS, PRELOADER_HTML} from "@/modules/preloader.js";
import {isMobile} from "@/utils/mobile.js";
import {updateQueryString} from "@/utils/query-string.js";

const body = $('body');

const loadRecords = ({container, conditions, link}) => {

    $(container).append(PRELOADER_HTML);

    const request = {
        ...Object.fromEntries(new URLSearchParams(link ? link.split('?')[1] : window.location.search)),
        year: $('.records-list__years').find('.top-list__item--active').data('year'),
        month: $('.records-list__months').find('.top-list__item--active').data('month'),
        sort: isMobile() ? $('.records-list__sort__mobile option:selected').val() : $(container).find('.records-list__sort__items .top-list__item--active').data('sort'),
        page: 1,
        search: $(container).find('.records-list__sort__search input').val(),
    };

    const params = {
        conditions,
       ...request
    }

    if ($(container).data('block-title')) {
        params.block_title = $(container).data('block-title');
    }
    if ($(container).data('title-param')) {
        params.title_param = $(container).data('title-param');
    }

    $.post(route('records.ajax'), params).done((res) => {
        $('html,body').animate({
            scrollTop:  $(container).offset().top
        }, 300);
        $(container).find(`.${PRELOADER_CLASS}`).remove();
        $(container).html(res.data.html);

        updateQueryString(request);
    })
};
$(body).on('click', '.records-list__ajax-container .top-list__item', function(e) {
    $(this).parents('.top-list').find('.top-list__item').removeClass('top-list__item--active');
    $(this).addClass('top-list__item--active');
});

$(body).on('click', '.records-list__ajax-container .records-list__filters a, .records-list__ajax-container .records-list__outer .page-link', function(e) {
    const container = $(this).parents('.records-list__outer');
    const conditions = $(container).data('conditions');
    const link = $(this).attr('href');
    e.preventDefault();
    loadRecords({container, conditions, link})
});
$(body).on('change', '.records-list__ajax-container .records-list__sort__search input, .records-list__ajax-container .records-list__sort__mobile', function(e) {
    let container = $(this).parents('.records-list__outer');
    const conditions = $(container).data('conditions');
    const search = $(this).val();
    loadRecords({container, conditions, search});
});
