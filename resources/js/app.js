import './init'
import Vue from 'vue';

import 'jquery-pjax';
import './jquery-ui.min'

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

import './bbcodes'
import './uVideoPlayer'
import './vue-components'

import './modules/articles'
import './modules/awards'
import './modules/channels'
import './modules/comments'
import './modules/forms'
import './modules/forum'
import './modules/modals'
import './modules/pages'
import './modules/reputation'
import './modules/tabs'
import './modules/warnings'
import './modules/pm'
import './modules/player'
import './modules/common'
import './modules/notifications'
import './modules/records'
import './modules/profile'
import './modules/approve'
import './modules/programs'
import './modules/theme-dark'
import './modules/categories'
import './modules/advertising'
import './modules/share'
import './modules/splashscreen'
import './modules/captcha'
import './modules/search'
import './modules/mobile-menu'
import './modules/playlist'
import './modules/survey'
import './modules/previews'
import './modules/teletext'

function onPageChange() {
    let script = $('#pjax_scripts_container').data('script');
    if (script) {
        script = script.replace('<script>', '');
        script = script.replace('</script>', '');
        eval(script);
    }

    const needInitializeVue = $('#pjax-content').data('vue');
    const onMounted = () =>  window.execOnMounted.forEach(fn => fn());
    if (needInitializeVue) {
        window._vm = new Vue({
            el: '#app',
            mounted: () => onMounted()
        });
    } else {
        onMounted()
    }
}

const onReady = () => {
    $(document).pjax('a[target!="_blank"]', '#pjax-container', {timeout: 10000});
    onPageChange();

    let lastLoadedUrl = window.location.href;

    let isPaginationRequest = false;
    let paginationScrollTop;

    $(document).on('pjax:start', (e) => {
        isPaginationRequest = lastLoadedUrl.split('?')[0] === window.location.href.split('?')[0];
        if (isPaginationRequest) {
            paginationScrollTop = $(e.relatedTarget).closest('.box')[0].offsetTop;
        }

        $('body').addClass('page-loading');
    });
    $(document).on('pjax:success', () => {
        lastLoadedUrl = window.location.href;
        window.recaptchaLoaded = false;
        $('body').removeClass('page-loading');
        onPageChange();

        if (isPaginationRequest) {
            window.scrollTo({
                top: paginationScrollTop,
            })
        }
    });
    $(document).on('pjax:popstate', () => {
        setTimeout(() => {
            $('body').removeClass('page-loading');
            $('.form__preloader').remove();
        }, 250);
    });
};
$(document).ready(function() {
    $.ajaxSetup({headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}});

    onReady();
});
