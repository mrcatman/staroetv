import { showModal, showModalAjax } from './modals';
const body = $('body');

$(body).on('click', '.user-page__info-block__value--reputation', function() {
    showModalAjax($.get(route('reputation.ajax'), {user_id: $('.user-page').data('user-id')}), `#reputation_history_${$(this).data('user-id')}`, 'Репутация пользователя');
});

$(body).on('click', '.forum-message__reputation__number', function() {
    showModalAjax($.get(route('reputation.ajax'), {user_id: $(this).data('user-id')}), `#reputation_history_${(this).data('user-id')}`, 'Репутация пользователя');
});
$(body).on('click', '.forum-message__reputation__change', function() {
    showModal('#change_reputation');
    $('#change_reputation input[name="user_id"]').val($(this).data('user-id'));
    $('#change_reputation input[name="forum_message_id"]').val($(this).parents('.forum-message').attr('id'));
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
    const item = $(`.reputation-history__item[data-id=${res.data.reputation_item.id}]`);
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
    const item = $(`.reputation-history__item[data-id=${res.data.reputation_item.id}]`);
    $(item).find('.reputation-history__item__reply-form').hide();
    $(item).find('.reputation-history__item__reply-comment').show();
    $(item).find('.reputation-history__item__reply-comment__text').html(res.data.reputation_item.reply_comment);
}
window.replyReputationCallback = replyReputationCallback;

$(body).on('click', '.reputation-history__item__button--delete', function() {
    const id = $(this).parents('.reputation-history__item').data('id');
    if (confirm("Вы уверены, что хотите удалить это сообщение?")) {
        $.post(route('reputation.delete'), {id}).done(res => {
            if (res.status) {
                $(this).parents('.reputation-history__item').remove();
            } else {
                alert(res.text);
            }
        })
    }
});
