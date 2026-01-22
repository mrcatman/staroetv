import $ from 'jquery'
import select2 from 'select2';
import 'select2/dist/css/select2.css';
import { Editor } from "@tiptap/vue-3";
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
window.$.each( [ "put", "delete" ], function( i, method ) {
    $[ method ] = function( url, data, callback, type ) {
        if ( $.isFunction( data ) ) {
            type = type || callback;
            callback = data;
            data = undefined;
        }

        return $.ajax({
            url: url,
            type: method,
            dataType: type,
            data: data,
            success: callback
        });
    };
});

declare global {
    interface Window {
        execOnMounted: (() => void)[],
        activeEditors: {
            [key: string]: Editor
        }
    }
}

window.execOnMounted = [];
window.activeEditors = {};
