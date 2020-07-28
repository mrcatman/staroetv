let body = $('body');

function setCookie(name,value,days) {
    var expires = "";
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days*24*60*60*1000));
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + (value || "")  + expires + "; path=/";
}

$(body).on('click', '.footer__light-switch', function () {
    $(body).toggleClass('theme-dark');
    setCookie('theme-dark', $(body).hasClass('theme-dark') ? '1' : '0', 365);
});
