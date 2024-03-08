window.$ = require('jquery');
window.jQuery = window.$;

window.$.post = function(url, data, success, args) {
    args = $.extend({
        url: url,
        type: 'POST',
        data: JSON.stringify(data),
        contentType: 'application/json; charset=utf-8',
        dataType: 'json',
        async: true,
        success: success
    }, args);
    return $.ajax(args);
};


require('jquery-pjax');
require('jquery-ui-bundle');

window.execOnMounted = [];
window.Vue = require('vue');
require ('./vue-components');

require ('./bbcodes');
require ('./uVideoPlayer');

import 'select2';
import 'select2/dist/css/select2.css';

require('./modules/articles');
require('./modules/awards');
require('./modules/channels');
require('./modules/comments');
require('./modules/forms');
require('./modules/forum');
require('./modules/modals');
require('./modules/pages');
require('./modules/reputation');
require('./modules/tabs');
require('./modules/warnings');
require('./modules/pm');
require('./modules/player');
require('./modules/common');
require('./modules/notifications');
require('./modules/records');
require('./modules/profile');
require('./modules/approve');
require('./modules/programs');
require('./modules/theme-dark');
require('./modules/categories');
require('./modules/advertising');
require('./modules/share');
require('./modules/splashscreen');
require('./modules/captcha');
require('./modules/search');
require('./modules/mobile-menu');
require('./modules/playlist');
require('./modules/survey');

let onReady = () => {
    $(document).pjax('a[target!="_blank"]', '#pjax-container', {timeout: 10000});
    onPageChange();
    function onPageChange() {
        let script = $('#pjax_scripts_container').data('script');
        if (script) {
            script = script.replace('<script>', '');
            script = script.replace('</script>', '');
            eval(script);
        }
        window._vm = new Vue({
            el: '#app',
            mounted: () => {
                window.execOnMounted.forEach(fn => {
                    fn();
                })
            }
        });
    }
    $(document).on('pjax:start', () => {
       // $('body').addClass('page-loading');
    });
    $(document).on('pjax:success', () => {
        window.recaptchaLoaded = false;
       //$('body').removeClass('page-loading');
        onPageChange();
    });
    $(document).on('pjax:popstate', () => {
        setTimeout(() => {
         //   $('body').removeClass('page-loading');
            $('.form__preloader').remove();
        }, 250);
    });
};
$(document).ready(function() {
    onReady();
});
