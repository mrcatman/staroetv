let body = $('body');

function setCookie(name,value,days) {
    let expires = "";
    if (days) {
        let date = new Date();
        date.setTime(date.getTime() + (days*24*60*60*1000));
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + (value || "")  + expires + "; path=/";
}

$(body).on('click', '.footer__light-switch', function () {
    let dark = $(body).hasClass('theme-dark');
    $(body).removeClass('theme-dark theme-light').addClass(dark ? 'theme-light' : 'theme-dark');
    setCookie('theme-dark', !dark ? '1' : '0', 365);
});
