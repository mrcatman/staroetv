import {showModalAjax} from "@/modules/modals.js";

const body = $('body');

let telegramUserData;
$(body).on('click', '.button--telegram', function(e) {
    e.preventDefault();
    window.Telegram.Login.auth(
        { bot_id: import.meta.env.VITE_TELEGRAM_BOT_ID },
        (data) => {
            if (!data) {
                return;
            }
            telegramUserData = data;
            if ($(this).data('action') === 'register') {
                showTelegramRegisterForm();
            } else {
                const form = $(this).parents('.form');
                $(form).find('input[name="telegram_data"]').val(JSON.stringify(data));
                $(form).submit();
                $(form).find('input[name="telegram_data"]').val('');
            }
        }
    );
})

const showTelegramRegisterForm = () => {
    showModalAjax($.get(route('profile.telegram.register-form'), {telegram_data: telegramUserData}), '#login');

}

window.loginCallback = (response) => {
    if (response.is_new_user) {
        showTelegramRegisterForm();
    }
}

$(body).on('click', '.telegram-create-new-profile-link', function(e) {
    e.preventDefault();
    $('.telegram-create-new-profile-form').css('display', '');
    $('.telegram-connect-existing-profile-form').css('display', 'none');
})

$(body).on('click', '.telegram-connect-existing-profile-link', function(e) {
    e.preventDefault();
    $('.telegram-connect-existing-profile-form').css('display', '');
    $('.telegram-create-new-profile-form').css('display', 'none');
})
