import {showModal} from "@/modules/modals";
const body = $('body');

window.execOnMounted.push(function() {
    // if ($('#editor').length && typeof CKEDITOR !== 'undefined') {
    //     CKEDITOR.config.allowedContent = true;
    //     CKEDITOR.replace('editor');
    // }
    $('a').removeClass('link--active');
    $(`a[href="${window.location.pathname}"]`).addClass('link--active');
    $('.select').select2();
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
