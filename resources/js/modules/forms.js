let body = $('body');
$(".select").select2();

$.each( [ "put", "delete" ], function( i, method ) {
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

function replaceDom(dom) {
    dom.forEach(replacement => {
        if (replacement.prepend_to) {
            $(replacement.prepend_to).prepend(replacement.html);
        }
        if (replacement.append_to) {
            $(replacement.append_to).append(replacement.html);
        }
        if (replacement.replace) {
            $(replacement.replace).html(replacement.html);
        }
    })
}

$(body).on('click', '.captcha', function() {
    $(this).attr('src', $(this).attr('src'));
});

$(body).on('submit', '.form', function (e) {
    e.preventDefault();
    const url = $(this).attr('action') || window.location.pathname;
    $('#editor').each(function () {
        let $textarea = $(this);
        if (CKEDITOR.instances['editor']) {
            $textarea.val(CKEDITOR.instances['editor'].getData());
        }
    });

    const data = $(this).serializeArray();
    $(this).find('input[type="checkbox"]').each(function() {
        if ($(this).attr('name') !== "") {
            data.push({'name': $(this).attr('name'), 'value': $(this).is(':checked')});
        }
    });
    $(this).append('<div class="form__preloader"><img src="/pictures/ajax.gif"></div>');
    let formData = {};
    data.forEach(item => {
        formData[item.name] = item.value;
    });

    $(this).find('.input-container').removeClass('input-container--with-errors');
    $(this).find('.input-container__message').html('');
    $.ajax(url, {
        data : JSON.stringify(formData),
        contentType : 'application/json',
        type: 'POST'
    }).done((res) => {
        $(this).find('.form__preloader').remove();
        if (res.status) {
            if ($(this).data('auto-close-modal')) {
                setTimeout(() => {
                    if ($(this).parents('.modal-window').length > 0) {
                        if ($(this).data('reset')) {
                            $(this).trigger('reset');
                        }
                        $(this).parents('.modal-window').find('.modal-window__close').click();
                    }
                }, 2500)
            } else {
                if ($(this).data('reset')) {
                    $(this).trigger('reset');
                }
            }
            $(this).find('.response').removeClass('response--error').addClass('response--success').html(res.text);
            if (res.redirect_to) {
                setTimeout(() => {
                    window.location.href = res.redirect_to;
                }, 1250)
            }
            if (res.data && res.data.dom) {
                replaceDom(res.data.dom);
            }
            if ($(this).data('callback')) {
                window[$(this).data('callback')](res);
            }
        } else {
            $(this).find('.response').removeClass('response--success').addClass('response--error').html(res.text);
            if (res.errors) {
                Object.keys(res.errors).forEach(key => {
                    $(this).find('*[name=' + key + ']').parents('.input-container').addClass('input-container--with-errors').find('.input-container__message').html(res.errors[key].join("; "));
                })
            }
            if ($(this).data('callback')) {
                window[$(this).data('callback')](res);
            }
        }
        if (!$(this).data('noscroll')) {
            $(this).find('.response')[0].scrollIntoView();
        }
    })
        .fail((xhr) => {
            $(this).find('.form__preloader').remove();

            let error = xhr.responseJSON;
            if (error.message === "") {
                $(this).find('.response').removeClass('response--success').addClass('response--error').html("Неизвестная ошибка");
            } else {
                $(this).find('.response').removeClass('response--success').addClass('response--error').html(error.message);
                Object.keys(error.errors).forEach(key => {
                    $(this).find('*[name=' + key + ']').parents('.input-container').addClass('input-container--with-errors').find('.input-container__message').html(error.errors[key].join("; "));
                })
            }
            if (!$(this).data('noscroll')) {
                $(this).find('.response')[0].scrollIntoView();
            }
        });
});

$(body).on('change', '.input-container--checkbox--toggle input[type="checkbox"]', function(e) {
    if ($(this).is(':checked')) {
        $(this).parents('.input-container--checkbox--toggle').find('.input').attr('disabled', false);
    } else {
        $(this).parents('.input-container--checkbox--toggle').find('.input').attr('disabled', true);
    }
});