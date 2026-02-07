import { setCookie } from "../utils/cookies";

const body = $('body');

$(body).on('click', '.footer__light-switch', function () {
    let dark = $(body).hasClass('theme-dark');
    $(body).removeClass('theme-dark theme-light').addClass(dark ? 'theme-light' : 'theme-dark');
    setCookie('theme-dark', !dark ? '1' : '0', 365);
});
