import replaceDom from './replaceDom';
$('body').on('click', '.input-container__toggle-button--mass-send', function() {
    let activeClass = 'input-container__toggle-button--active';
    if (!$(this).hasClass(activeClass)) {
        $(this).addClass(activeClass);
        $(this).parents('.input-container').find('.input-container__disabled-overlay').show();
        $(this).parents('.input-container').find('input[name="is_group"]').val(1);
        $('#users_groups_select_container').show();
    } else {
        $(this).removeClass(activeClass);
        $(this).parents('.input-container').find('.input-container__disabled-overlay').hide();
        $(this).parents('.input-container').find('input[name="is_group"]').val(0);
        $('#users_groups_select_container').hide();
    }
});

window.pm = {
    updateCount() {
        $.post('/pm/update').done(res => {
            if (res.status) {
                replaceDom(res.data.dom);
            } else {
                alert(res.text);
            }
        })
    }
};
