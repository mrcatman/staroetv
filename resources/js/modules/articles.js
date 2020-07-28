import { showModal } from './modals';
import replaceDom from "./replaceDom";
let body = $('body');
$(body).on('click', '.button--delete-article', function() {
    if ($(this).data('id')) {
        $('input[name="article_id"]').val($(this).data('id'));
    }
    showModal('#delete_article');
});
$(body).on('click', '.button--approve-article', function() {
    if ($(this).data('id')) {
        $('input[name="article_id"]').val($(this).data('id'));
    }
    showModal('#approve_article');
});

$(body).on('click', '.button--article-menu', function() {
    let id = $(this).data('id');
    $.post('/articles/actions', {id}).done(res => {
        if (res.status) {
            replaceDom(res.data.dom);
        } else {
            alert(res.text);
        }
    })
});

$(body).on('click', '*[data-change-article-type]', function () {
    $.post('/articles/change-type', {type_id: $(this).data('change-article-type'), id: $(this).data('change-article-type-id')}).done(res => {
        if (res.status) {
            $.pjax.reload('#pjax-container')
        } else {
            alert(res.text);
        }
    })
});
