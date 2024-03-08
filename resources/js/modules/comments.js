import replaceDom from './replaceDom';
import { showModal, showModalAjax } from './modals';
let body = $('body');

$(body).on('click', '.comments__pager .page-link', function(e) {
    const page = $(this).attr('href').split('?page=')[1];
    const conditions = $(this).parents('.comments').data('conditions');
    if (!conditions) {
        return;
    }
    e.preventDefault();
    $(this).parents('.comments').append('<div class="block-preloader"><img src="/pictures/ajax.gif"></div>');
    $.post('/comments/ajax', {page, conditions})
        .done((res) => {
            $(this).parents('.comments').find('.block-preloader').remove();
            $(this).parents('.comments__main').html(res.data.html);
        })
});

$(body).on('click', '.comment__reply', function() {
    let id = $(this).parents('.comment').data('id');
    let form = $('.comments > .comments__form');
    let formContainer = $('#reply_form_container');
    $(formContainer).html($(form).html());
    $(formContainer).find('input[name="parent_id"]').val(id);
    $(formContainer).find('#message').attr('id', 'message_modal');
    $(form).append('<div class="comments__form__disable"></div>')
    showModal('#reply_form_container', 'Ответить на комментарий', () => {
        bb.init('message');
        $('.comments__form__disable').remove();
    });
    bb.init('message_modal');
});

$(body).on('click', '.bb-editor__smiles__all', function() {
    showModalAjax($.post('/smiles'), '#all_smiles');
});

$(body).on('click', '.comment__edit', function() {
    let id = $(this).parents('.comment').eq(0).data('id');
    let formContainer = $('#edit_form_container');
    let form = $('.comments > .comments__form');
    $(formContainer).html($(form).html());
    $(formContainer).find('input[name="parent_id"]').val(id);
    $(formContainer).find('textarea[name="message"]').attr('id', 'message_edit_modal');
    $(formContainer).find('textarea[name="message"]').val($(this).parents('.comment').eq(0).find('.comment__original-text').html().trim());
    $(formContainer).find('form').attr('action', '/comments/edit');
    $(formContainer).find('input[name="id"]').val(id);
    $(form).append('<div class="comments__form__disable"></div>')
    showModal('#edit_form_container', 'Редактировать комментарий', () => {
        bb.init('message');
        $('.comments__form__disable').remove();
    });
    bb.init('message_edit_modal');
});

$(body).on('click', '.comment__delete', function() {
    let id = $(this).parents('.comment').eq(0).data('id');
    if (confirm("Вы уверены, что хотите удалить комментарий?")) {
        $.post('/comments/delete', {id}).done(res => {
            if (res.status) {
                replaceDom(res.data.dom);
            } else {
                alert(res.text);
            }
        })
    }
});

$(body).on('click', '.comment__rating__button', function() {
    let comment_id = $(this).parents('.comment').eq(0).data('id');
    let weight = !$(this).hasClass('comment__rating__button--minus') ? 1 : -1;
    $.post('/comments/rating', {comment_id, weight}).done(res => {
        if (res.status) {
            replaceDom(res.data.dom);
        } else {
            alert(res.text);
        }
    })
});

window.execOnMounted.push(function() {
    $('.comment__video-player, .forum-message__video-player').each(function() {
        let params = $(this).data('params');
        params = params.replace(/'/g, '"');
        console.log(params);
        params = JSON.parse(params);
        _uVideoPlayer(params, $(this).data('element'));
    });
})


window.execOnMounted.push(function() {
    $('.comments--lazyload').each(function() {
          const conditions = $(this).data('conditions');
          $(this).addClass('comments--loading');
          $(this).append('<div class="comments__preloader"><img src="/pictures/ajax.gif"></div>');
          $.post('/comments/ajax', {page: 1, conditions, count: true})
              .done((res) => {
                  $(this).removeClass('comments--loading');
                  $(this).parents('.box').find('.box--comments__loading').removeClass('box--comments__loading').html(res.data.count);
                  $(this).find('.comments__preloader').remove();
                  $(this).find('.comments__main').html(res.data.html);
              })
    })

    $('.bb-editor__text').each(function () {
        this.setAttribute('style', 'height:' + (this.scrollHeight) + 'px;overflow-y:hidden;');
    }).on('input', function () {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    });
});
