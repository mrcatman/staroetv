import replaceDom from './replaceDom';
import { showModal, showModalAjax } from './modals';
let body = $('body');

let loadRecords = ({container, conditions, page, sort, search}) => {
    $(container).append('<div class="block-preloader"><img src="/pictures/ajax.gif"></div>');
    let data = {page, conditions, sort};
    if (!data.sort) {
        sort = $(container).find('.records-list__sort__item--active').data('sort');
    }
    if (!data.page) {
        page = 1;
    }
    if (!search) {
        search = $(container).find('.records-list__sort__search input').val();
    }
    if (search && search !== '') {
        data.search = search;
    }
    if ($(container).data('block-title')) {
        data.block_title = $(container).data('block-title');
    }
    if ($(container).data('title-param')) {
        data.title_param = $(container).data('title-param');
    }
    $.post('/records/ajax', data).done((res) => {
        $('html,body').animate({
            scrollTop:  $(container).offset().top
        }, 300);
        $(container).find('.block-preloader').remove();
        $(container).html(res.data.html);
    })
};

$(body).on('click', '.records-list__outer .page-link', function(e) {
    const page = $(this).attr('href').split('?page=')[1];
    let container = $(this).parents('.records-list__outer');
    const conditions = $(container).data('conditions');
    if (!conditions) {
        return;
    }
    e.preventDefault();
    loadRecords({container, conditions, page});
});

$(body).on('click', '.records-list__sort__item', function(e) {
    let container = $(this).parents('.records-list__outer');
    const conditions = $(container).data('conditions');
    const sort = $(this).data('sort');
    loadRecords({container, conditions, sort});
});
$(body).on('change', '.records-list__sort__search input', function(e) {
    let container = $(this).parents('.records-list__outer');
    const conditions = $(container).data('conditions');
    const search = $(this).val();
    loadRecords({container, conditions, search});
});


