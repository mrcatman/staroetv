let body = $('body');
$(body).on('change', 'select[name="user_group"]', function() {
    $.post('/admin/users/change-group', {group_id: $(this).val(), user_id: $(this).data('user-id')}).done(res => {
        if (!res.status) {
            alert(res.text);
        } else {
            window.location.reload();
        }
    })
});
