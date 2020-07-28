import replaceDom from './replaceDom';
import {showModal, showModalAjax} from './modals';
let body = $('body');

$(body).on('click', '.forum-section__delete-topic', function() {
    showModal('#delete_topic');
});


$(body).on('click', '.forum-section__move-topic', function() {
    showModal('#move_topic');
});

$(body).on('click', '.forum-message__edit', function() {
    let message = $(this).parents('.forum-message');
    let message_id = $(message).data('id');
    $.post('/forum/get-edit-form', {message_id}).done(res => {
        $(message).find('.forum-message__content').hide();
        $(message).find('.forum-message__edit-form').show().html(res.data.html);
    });
});

$(body).on('click', '.forum-message__delete', function() {
    let message = $(this).parents('.forum-message');
    let message_id = $(message).data('id');
    if (confirm("Вы уверены, что хотите удалить это сообщение?")) {
        $.post('/forum/delete-message', {message_id}).done(res => {
            if (res.status) {
                replaceDom(res.data.dom);
            } else {
                alert(res.text);
            }
        })
    }
});

$(body).on('click', '.forum-message .bb-editor__cancel', function() {
    let message = $(this).parents('.forum-message');
    $(message).find('.forum-message__content').show();
    $(message).find('.forum-message__edit-form').hide();
});

$(body).on('change', '#forum_state', function() {
    if ($(this).val() == "4") {
        $('#forum_move_to').show();
    } else {
        $('#forum_move_to').hide();
    }
})

function forumMessageCallback(res) {
    if (res.status) {
        let currentPage = parseInt($('.pagination').eq(0).find('.page-item.active .page-link').text().trim());
        let lastPage = parseInt(res.data.last_page);
        if (currentPage !== lastPage) {
            let topic_id = $('.forum-section').data('topic-id');
            let forum_id = $('.forum-section').data('forum-id');
            let url = "/forum/"+forum_id+"-"+topic_id+"-"+lastPage+"#"+res.data.message.id;
            $.pjax({url: url, container: '#pjax-container'})
        } else {
            replaceDom(res.data._dom);
        }
    }
}
window.forumMessageCallback = forumMessageCallback;

$(body).on('change', 'input[name="questionnaire"]', function() {
    $(this).is(':checked') ? $('#questionnaire_editor_container').show() : $('#questionnaire_editor_container').hide();
});

$(body).on('click', '.forum-page .page-link', function() {
    $('.forum-page').append('<div class="form__preloader"><img src="/pictures/ajax.gif"></div>');
});

$(body).on('click', '.forum-message__show-profile', function() {
    let message_id = $(this).data('message-id');
    showModalAjax($.get('/forum/profile/' + message_id), '#message_profile_' + message_id);
});

window.execOnMounted.push(() => {
    let index =  1000;
    $('.forum-message').each(function() {
        $(this).css('zIndex', index);
        index--;
    })
})
