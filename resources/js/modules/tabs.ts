const body = $('body');

$(body).on('click', '.tab', function() {
    const id = $(this).parents('.tabs').data('id');
    const tab = $(this).data('content');
    $(this).parents('.tabs').find('.tab').removeClass('tab--active');
    $(this).addClass('tab--active');
    $(body).find(`.tab-content[data-id="${id}"]`).hide();
    $(body).find(`.tab-content[data-id="${id}"][data-tab="${tab}"]`).show();
});

$(body).on('click', 'a[data-show-block-selector]', function() {
    const selector = $(this).data('show-block-selector');
    const id = $(this).data('show-block-id');
    const toggleClass = $(this).data('toggle-class');
    if (toggleClass) {
        $($(this).data('selector')).removeClass(toggleClass);
        $(this).addClass(toggleClass);
    }
    $(selector).hide();
    $(selector + `[data-block-id=${id}]`).show();
});
