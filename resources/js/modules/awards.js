import { showModalAjax } from './modals';
let body = $('body');

$(body).on('click', '.user-page__info-block__value--awards', function() {
    let user_id = $('.user-page').data('user-id');
    showModalAjax($.post('/awards/ajax', {user_id}), '#awards_history_' + user_id, 'Награды пользователя');
});

$(body).on('click', '.user-page__info-block__change--awards', function() {
    let user_id = $('.user-page').data('user-id');
    showModalAjax($.post('/awards/list', {user_id}), '#awards_list', 'Выдать награду');
});

$(body).on('click', '.awards-history__item__button--edit', function() {
    $(this).parents('.awards-history__item').find('.awards-history__item__form').show();
    $(this).parents('.awards-history__item').find('.awards-history__item__comment').hide();
});

$(body).on('click', '.awards-history__item__form .button--cancel', function() {
    $(this).parents('.awards-history__item').find('.awards-history__item__form').hide();
    $(this).parents('.awards-history__item').find('.awards-history__item__comment').show();
});
function editAwardCallback(res) {
    let id = res.data.award.id;
    let item = $('.awards-history__item[data-id='+id+']');
    $(item).find('.awards-history__item__form').hide();
    $(item).find('.awards-history__item__comment').show().html(res.data.award.comment);
}
window.editAwardCallback = editAwardCallback;
$(body).on('click', '.awards-history__item__button--delete', function() {
    let id = $(this).parents('.awards-history__item').data('id');
    if (confirm("Вы уверены, что хотите удалить эту награду?")) {
        $.post('/awards/delete', {id}).done(res => {
            if (res.status) {
                $(this).parents('.awards-history__item').remove();
            } else {
                alert(res.text);
            }
        })
    }
});

$(body).on('click', '.forum-message__awards__number', function() {
    let user_id = $(this).data('user-id');
    showModalAjax($.post('/awards/ajax', {user_id}), '#awards_history_' + user_id);
});