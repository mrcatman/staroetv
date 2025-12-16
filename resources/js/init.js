import $ from 'jquery'
import select2 from 'select2';
import 'select2/dist/css/select2.css';
select2($);

window.jQuery = window.$ = $
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

window.execOnMounted = [];
