import { showModal, showModalAjax } from './modals';
let body = $('body');

$(body).on('click', '.user-page__info-block__value--reputation', function() {
    let user_id = $('.user-page').data('user-id');
    showModalAjax($.post('/reputation/ajax', {user_id}), '#reputation_history_' + user_id, 'Репутация пользователя');
});

$(body).on('click', '.forum-message__reputation__number', function() {
    let user_id = $(this).data('user-id');
    showModalAjax($.post('/reputation/ajax', {user_id}), '#reputation_history_' + user_id, 'Репутация пользователя');
});
$(body).on('click', '.forum-message__reputation__change', function() {
    let user_id = $(this).data('user-id');
    let message_id = $(this).parents('.forum-message').attr('id');
    showModal('#change_reputation');
    $('#change_reputation input[name="user_id"]').val(user_id);
    $('#change_reputation input[name="forum_message_id"]').val(message_id);
});

$(body).on('click', '.user-page__info-block__change--reputation', function() {
    showModal('#change_reputation');
    $('#change_reputation input[name="user_id"]').val($('.user-page').data('user-id'));
});
$(body).on('click', '.reputation-history__item__button--edit', function() {
    $(this).parents('.reputation-history__item').find('.reputation-history__item__form').show();
    $(this).parents('.reputation-history__item').find('.reputation-history__item__comment').hide();
});

$(body).on('click', '.reputation-history__item__form .button--cancel', function() {
    $(this).parents('.reputation-history__item').find('.reputation-history__item__form').hide();
    $(this).parents('.reputation-history__item').find('.reputation-history__item__comment').show();
});
function editReputationCallback(res) {
    let id = res.data.reputation_item.id;
    let item = $('.reputation-history__item[data-id='+id+']');
    $(item).find('.reputation-history__item__form').hide();
    $(item).find('.reputation-history__item__comment').show().html(res.data.reputation_item.comment);
}
window.editReputationCallback = editReputationCallback;

$(body).on('click', '.reputation-history__item__button--reply', function() {
    $(this).parents('.reputation-history__item').find('.reputation-history__item__reply-comment').hide();
    $(this).parents('.reputation-history__item').find('.reputation-history__item__reply-form').show();
});

$(body).on('click', '.reputation-history__item__reply-form .button--cancel', function() {
    $(this).parents('.reputation-history__item').find('.reputation-history__item__reply-form').hide();
    if ($(this).parents('.reputation-history__item').find('.reputation-history__item__reply-comment__text').length > 0) {
        $(this).parents('.reputation-history__item').find('.reputation-history__item__reply-comment').show();
    }
});

function replyReputationCallback(res) {
    let id = res.data.reputation_item.id;
    let item = $('.reputation-history__item[data-id='+id+']');
    $(item).find('.reputation-history__item__reply-form').hide();
    $(item).find('.reputation-history__item__reply-comment').show();
    $(item).find('.reputation-history__item__reply-comment__text').html(res.data.reputation_item.reply_comment);
}
window.replyReputationCallback = replyReputationCallback;

$(body).on('click', '.reputation-history__item__button--delete', function() {
    let id = $(this).parents('.reputation-history__item').data('id');
    if (confirm("Вы уверены, что хотите удалить это сообщение?")) {
        $.post('/reputation/delete', {id}).done(res => {
            if (res.status) {
                $(this).parents('.reputation-history__item').remove();
            } else {
                alert(res.text);
            }
        })
    }
});
