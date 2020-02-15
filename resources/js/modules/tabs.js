let body = $('body');

$(body).on('click', '.tab', function() {
    let id = $(this).parents('.tabs').data('id');
    let tab = $(this).data('content');
    $(this).parents('.tabs').find('.tab').removeClass('tab--active');
    $(this).addClass('tab--active');
    $(body).find('.tab-content[data-id="'+id+'"]').hide();
    $(body).find('.tab-content[data-id="'+id+'"][data-tab="'+tab+'"]').show();
});

$(body).on('click', 'a[data-show-block-selector]', function() {
    let selector = $(this).data('show-block-selector');
    let id = $(this).data('show-block-id');
    let toggleClass = $(this).data('toggle-class');
    if (toggleClass) {
        let tabSelector = $(this).data('selector');
        $(tabSelector).removeClass(toggleClass);
        $(this).addClass(toggleClass);
    }
    $(selector).hide();
    $(selector + '[data-block-id='+id+']').show();
});
