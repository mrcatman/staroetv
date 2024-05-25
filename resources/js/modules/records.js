
const body = $('body');

const loadRecords = ({container, conditions, link}) => {

    $(container).append('<div class="block-preloader"><img src="/pictures/ajax.gif"></div>');


    const data = Object.fromEntries(new URLSearchParams(link ? link.split('?')[1] : window.location.search));
    if (!data.sort) {
        data.sort = $(container).find('.records-list__sort__item--active').data('sort');
    }
    if (!data.sort) {
        data.sort = $('.records-list__sort__mobile option:selected').val();
    }
    if (!data.page) {
        data.page = 1;
    }
    if (!data.search) {
        const search = $(container).find('.records-list__sort__search input').val();
        if (search && search.trim().length) {
            data.search = search;
        }
    }
    if (!data.year) {
        const year = $('.records-list__years').find('.top-list__item--active').data('year');
        if (year && parseInt(year) > 0) {
            data.year = year;
        }
    }
    if (!data.month) {
        const month = $('.records-list__months').find('.top-list__item--active').data('month');
        if (month && parseInt(month) > 0) {
            data.month = month;
        }
    }
    const params = JSON.parse(JSON.stringify(data));
    params.conditions = conditions;
    if ($(container).data('block-title')) {
        params.block_title = $(container).data('block-title');
    }
    if ($(container).data('title-param')) {
        params.title_param = $(container).data('title-param');
    }
    $.post('/records/ajax', params).done((res) => {
        $('html,body').animate({
            scrollTop:  $(container).offset().top
        }, 300);
        $(container).find('.block-preloader').remove();
        $(container).html(res.data.html);

        const url = new URL(window.location.href);
        Object.keys(data).forEach(key => {
            url.searchParams.set(key, data[key]);
            window.history.pushState(null, '', url.toString());
        });
    })
};


$(body).on('click', '.records-list__filters a, .records-list__outer .page-link', function(e) {
    const container = $(this).parents('.records-list__outer');
    const conditions = $(container).data('conditions');
    const link = $(this).attr('href');
    loadRecords({container, conditions, link});
    e.preventDefault();
});
$(body).on('change', '.records-list__sort__search input', function(e) {
    let container = $(this).parents('.records-list__outer');
    const conditions = $(container).data('conditions');
    const search = $(this).val();
    loadRecords({container, conditions, search});
});


