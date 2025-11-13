import {showModal} from "@/modules/modals.js";
const body = $('body');

window.execOnMounted.push(function() {
    if ($('#editor').length > 0){
        CKEDITOR.config.allowedContent = true;
        CKEDITOR.replace('editor');
    }
    let pathname = window.location.pathname;
    $('a').removeClass('link--active');
    $('a[href="'+pathname+'"]').addClass('link--active');
})

$(body).on('click', '.button--login', function(e) {
    showModal('#login');
    e.preventDefault();
});

$(body).on('click', '.form__bottom__link', function() {
    if ($(this).attr('href')?.length) {
        $(this).parents('.modal-window').find('.modal-window__close').click();
    }
});
