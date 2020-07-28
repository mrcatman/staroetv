import {showModal} from "./modals";

let translit = require ('../translit');
let body = $('body');

$(body).on('change', '#channel_name', function() {
    let name = $(this).val();
    let transliterated = translit(name);
    $('#channel_url').val(transliterated);
});



$(body).on('click', '.channel-page__logos__list__item', function() {
    $('.channel-page__logos__list__item').removeClass('channel-page__logos__list__item--selected');
    $(this).addClass('channel-page__logos__list__item--selected');
    let data = $(this).data('info');
    $('.channel-page__selected-logo__picture').css('background-image', 'url('+data.logo.url+')');
    $('.channel-page__selected-logo__name').html(data.name);
    $('.channel-page__selected-logo__years').html(data.years_range);
    $('.channel-page__selected-logo__description').html(data.comment);
});


