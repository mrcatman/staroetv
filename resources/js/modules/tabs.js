let body = $('body');

$(body).on('click', '.tab', function() {
    let id = $(this).parents('.tabs').data('id');
    let tab = $(this).data('content');
    $(this).parents('.tabs').find('.tab').removeClass('tab--active');
    $(this).addClass('tab--active');
    $(body).find('.tab-content[data-id="'+id+'"]').hide();
    $(body).find('.tab-content[data-id="'+id+'"][data-tab="'+tab+'"]').show();
});