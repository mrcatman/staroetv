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