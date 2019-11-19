import { showModal, showModalAjax } from './modals';
let body = $('body');


$(body).on('click', '.user-page__info-block__value--warnings', function() {
    let user_id = $('.user-page').data('user-id');
    showModalAjax($.post('/warnings/ajax', {user_id}), '#warnings_history_' + user_id, 'Замечания пользователя');
});

$(body).on('click', '.user-page__info-block__change--warnings', function() {
    let user_id = $('.user-page').data('user-id');
    showModalAjax($.post('/warnings/form', {user_id}), '#warnings_form', 'Выдать замечание');
});


$(body).on('click', '.button--login', function(e) {
    showModal('#login');
    e.preventDefault();
});

$(body).on('click', '.form__bottom__link', function() {
    $(this).parents('.modal-window').find('.modal-window__close').click();
});

$(body).on('click', '.awards-list__item', function() {
    let id = $(this).data('id');
    $(this).parents('.awards-list').find('input[name="award_id"]').val(id);
    $(this).parents('.awards-list').find('.awards-list__form').show();
});

$(body).on('click', '.forum-message__warnings__number', function() {
    let user_id = $(this).data('user-id');
    showModalAjax($.post('/warnings/ajax', {user_id}), '#warnings_history_' + user_id);
});