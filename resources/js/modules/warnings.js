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

$(body).on('click', '.forum-message__warnings__number', function() {
    let user_id = $(this).data('user-id');
    showModalAjax($.post('/warnings/ajax', {user_id}), '#warnings_history_' + user_id);
});
