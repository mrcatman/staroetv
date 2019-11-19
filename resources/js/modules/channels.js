let translit = require ('../translit');
let body = $('body');

$(body).on('change', '#channel_name', function() {
    let name = $(this).val();
    let transliterated = translit(name);
    $('#channel_url').val(transliterated);
});

$(body).on('click', '.cities-list__item', function() {
    let city = $(this).data('city');
    $('.cities-list__item').removeClass('cities-list__item--active');
    $(this).addClass('cities-list__item--active');
    if ($(this).hasClass('cities-list__item--all')) {
        $(this).parents('.tab-content').find('.channel-item').show();
    } else {
        $(this).parents('.tab-content').find('.channel-item').hide();
        $(this).parents('.tab-content').find('.channel-item[data-city="'+city+'"]').show();
    }
})