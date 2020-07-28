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

function showVisibleCounters() {
    $('.auth-panel__button__count').each(function () {
        if ($(this).text().trim() == "0") {
            $(this).hide();
        } else {
            $(this).show();
        }
    })
}

window.pm = {
    updateCount() {
        $.post('/pm/update').done(res => {
            if (res.status) {
                replaceDom(res.data.dom);
                setTimeout(() => {
                    showVisibleCounters();
                }, 1)
            } else {
                alert(res.text);
            }
        })
    }
};

window.execOnMounted.push(function () {
    showVisibleCounters();
    let usersAutocomplete = $('#users_autocomplete');
    if ($(usersAutocomplete).length > 0) {
        $(usersAutocomplete).select2({
            ajax: {
                method: 'POST',
                url: '/users/autocomplete',
                dataType: 'json',
                processResults: function (data) {
                    return {
                        results: data.data.users.map(user => {
                            return {
                                id: user.id,
                                text: user.username,
                            }
                        }),
                        pagination: {
                            more: data.data.users.length > 0
                        }
                    };
                },
            }
        });
    }
})
