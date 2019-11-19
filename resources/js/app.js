window.$ = require('jquery');
window.jQuery = window.$;
require('jquery-pjax');
require('jquery-ui-bundle');

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

let onVueMounted = require('./modules/onmounted');

let onReady = () => {
    $(document).pjax('a[target!="_blank"]', '#pjax-container', {timeout: 10000});
    onPageChange();
    function onPageChange() {
        window._vm = new Vue({
            el: '#app',
            mounted: () => {
                onVueMounted();
            }
        });
    }

    $(document).on('pjax:success', () => {
        onPageChange();
    });


};
$(document).ready(function() {
    onReady();
});