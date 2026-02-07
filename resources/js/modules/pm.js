import { replaceHTML} from './replace-html';
import { language } from '@/utils/select2-language';

$('body').on('click', '.input-container__toggle-button--mass-send', function() {
    let activeClass = 'input-container__toggle-button--active';
    if (!$(this).hasClass(activeClass)) {
        $(this).addClass(activeClass);
        $(this).parents('.input-container').find('select').prop('disabled', true);

        $(this).parents('.input-container').find('input[name="is_group"]').val(1);
        $('#users_groups_select_container').show();
    } else {
        $(this).removeClass(activeClass);
        $(this).parents('.input-container').find('select').prop('disabled', false);
        $(this).parents('.input-container').find('input[name="is_group"]').val(0);
        $('#users_groups_select_container').hide();
    }
});

function showVisibleCounters() {
    $('.auth-panel__button__count, .mobile-menu__item__count').each(function () {
        if ($(this).text().trim() == "0") {
            $(this).hide();
        } else {
            $(this).show();
        }
    })
}

window.pm = {
    updateCount() {
        $.post(route('pm.update')).done(res => {
            if (res.status) {
                replaceHTML(res.data.html);
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
    const usersAutocomplete = $('#users_autocomplete');
    if ($(usersAutocomplete).length > 0) {
        $(usersAutocomplete).select2({
            language,
            ajax: {
                method: 'GET',
                url: route('users.autocomplete'),
                dataType: 'json',
                processResults: function ({ data }) {
                    return {
                        results: data.users.map(user => {
                            return {
                                id: user.id,
                                text: user.username,
                            }
                        }),
                        pagination: {
                            more: data.users.length > 0
                        }
                    };
                },
            }
        });
    }
})
