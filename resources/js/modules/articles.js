import { showModal } from './modals';
import { replaceHTML } from "./replace-html";

const body = $('body');

$(body).on('click', '.button--delete-article', function() {
    if ($(this).data('id')) {
        $('input[name="id"]').val($(this).data('id'));
    }
    showModal('#delete_article');
});
$(body).on('click', '.button--approve-article', function() {
    if ($(this).data('id')) {
        $('input[name="id"]').val($(this).data('id'));
    }
    showModal('#approve_article');
});

// $(body).on('click', '.button--article-menu', function() {
//     let id = $(this).data('id');
//     $.post(route('articles.get-actions'), {id}).done(res => {
//         if (res.status) {
//             replaceHTML(res.data.html);
//         } else {
//             alert(res.text);
//         }
//     })
// });

$(body).on('click', '*[data-change-article-type]', function () {
    $.post(route('articles.change-type'), {type_id: $(this).data('change-article-type'), id: $(this).data('change-article-type-id')}).done(res => {
        if (res.status) {
            $.pjax.reload('#pjax-container')
        } else {
            alert(res.text);
        }
    })
});
