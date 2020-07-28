let body = $('body');

$(body).on('click', '.region__name', function() {
    let container = $(this).parents('.regions__container');
    let region = $(this).data('region');
    $(container).find('.region').removeClass('region--active');
    $(this).parent().addClass('region--active');
    if ($(this).parent().hasClass('region--all')) {
        $(container).find('.channel-item').show();
    } else {
        $(container).find('.channel-item').hide();
        $(container).find('.channel-item[data-region="'+region+'"]').show();
    }
});

$(body).on('click', '.region__city', function() {
    let city = $(this).data('city');
    let container = $(this).parents('.regions__container');
    $(container).find('.region__city').removeClass('region__city--active');
    $(this).addClass('region__city--active');
    $(container).find('.region').removeClass('region--active');
    $(this).parents('.region').addClass('region--active');
    $(container).find('.channel-item').hide();
    $(container).find('.channel-item[data-city="'+city+'"]').show();
});


$(body).on('change', '.regions__search input', function() {
    let container = $(this).parents('.regions__container');
    $(container).find('.region__city').removeClass('region__city--active');
    $(container).find('.region').removeClass('region--active');
    let search = $(this).val().toLocaleLowerCase();
    $(container).find('.channel-item').each(function() {
        let name = $(this).text().trim().toLocaleLowerCase();
        if (name.indexOf(search) !== -1) {
            $(this).show();
        } else {
            $(this).hide();
        }
    })
});
