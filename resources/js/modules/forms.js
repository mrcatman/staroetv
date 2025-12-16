import replaceDom from './replaceDom';
import {showModal, showModalAjax} from "./modals";

const body = $('body');

const buildFormData = (formData, data, parentKey) => {
    if (data && typeof data === 'object' && !(data instanceof Date) && !(data instanceof File) && !(data instanceof Blob)) {
        Object.keys(data).forEach(key => {
            buildFormData(formData, data[key], parentKey ? `${parentKey}[${key}]` : key);
        });
    } else {
        const value = data == null ? '' : data;

        formData.append(parentKey, value);
    }
}

const jsonToFormData = (data) => {
    const formData = new FormData();

    buildFormData(formData, data);

    return formData;
}

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



$(body).on('click', '.captcha', function() {
    $(this).attr('src', $(this).attr('src'));
});

$(body).on('submit', '.form', function (e) {
    e.preventDefault();

    let confirmed = true;
    if ($(this).data('confirm')) {
        let text = $(this).data('confirm-text') || "Вы уверены?";
        if (!confirm(text)) {
            confirmed = false;
        }
    }

    if (!confirmed) {
        return;
    }

    const url = $(this).attr('action') || window.location.pathname;
    $('#editor').each(function () {
        let $textarea = $(this);
        if (CKEDITOR.instances['editor']) {
            $textarea.val(CKEDITOR.instances['editor'].getData());
        }
    });

    const data = $(this).serializeArray();
    const checkboxesData = {};
    $(this).find('input[type="checkbox"]').each(function () {
        if ($(this).attr('name') !== "") {
            if ($(this).attr('value') !== "" && $(this).attr('value') !== undefined) {
                let name = $(this).attr('name');
                if (!checkboxesData[name]) {
                    checkboxesData[name] = [];
                }
                if ($(this).is(':checked')) {
                    checkboxesData[name].push($(this).attr('value'));
                }
            } else {
                data.push({name: $(this).attr('name'), value: $(this).is(':checked')});
            }
        }
    });
    Object.keys(checkboxesData).forEach(name => {
        data.push({name, value: checkboxesData[name]})
    });
    $(this).find('input[type="file"]').each(function () {
        data.push({
            name: $(this).attr('name'),
            value: $(this).attr('multiple') ? $(this)[0].files : $(this)[0].files[0]
        })
    });

    $(this).append('<div class="form__preloader"><img src="/images/ajax.gif"></div>');
    let formData = {};
    data.forEach(item => {
        formData[item.name] = item.value;
    });

    $(this).find('.input-container').removeClass('input-container--with-errors');
    $(this).find('.input-container__message').html('');

    const response = $(this).find('.response');

    const isMultipart = $(this).attr('enctype') === 'multipart/form-data';

    const params = isMultipart ? {
        processData: false,
        contentType: false,
        data: jsonToFormData(formData),
        type: 'POST'
    } : {
        data: JSON.stringify(formData),
        contentType: 'application/json',
        type: 'POST'
    }

    const submit = () => {
        $.ajax(url, params).done((res) => {
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
                $(response).removeClass('response--error').addClass('response--success').html(res.text);
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
                if ($(this).data('reset')) {
                    $(this)[0].reset();
                }
            } else {
                if ($(response).length > 0) {
                    $(response).removeClass('response--success').addClass('response--error').html(res.text);
                } else {
                    alert(res.text);
                }
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
                if ($(response).length > 0) {
                    $(response)[0].scrollIntoView({behavior: "smooth", block: "center"});
                }
            }
        })
            .fail((xhr) => {
                $(this).find('.form__preloader').remove();
                let error = xhr.responseJSON;

                if (error.message === "") {
                    if ($(response).length > 0) {
                        $(response).removeClass('response--success').addClass('response--error').html("Неизвестная ошибка");
                    } else {
                        alert("Неизвестная ошибка");
                    }
                } else {
                    if ($(response).length > 0) {
                        $(response).removeClass('response--success').addClass('response--error').html(error.message);
                    } else {
                        alert(error.message);
                    }
                    Object.keys(error.errors).forEach(key => {
                        $(this).find('*[name=' + key + ']').parents('.input-container').addClass('input-container--with-errors').find('.input-container__message').html(error.errors[key].join("; "));
                    })
                }
                if (!$(this).data('noscroll')) {
                    if ($(response).length > 0) {
                        $(response)[0].scrollIntoView();
                    }
                }
            });
    };

    if ($(this).hasClass('form--with-captcha')) {
        grecaptcha.ready(function () {
            grecaptcha.execute('6LccwdUZAAAAANbvD4YOUIKQXR77BP8Zg5A-a9UT', {action: 'submit'}).then(function (token) {
                formData['g-recaptcha-response'] = token;
                submit();
            });
        });

    } else {
        submit();
    }

});

$(body).on('change', '.input-container--checkbox--toggle input[type="checkbox"]', function(e) {
    if ($(this).is(':checked')) {
        $(this).parents('.input-container--checkbox--toggle').find('.input').attr('disabled', false);
    } else {
        $(this).parents('.input-container--checkbox--toggle').find('.input').attr('disabled', true);
    }
});

$(body).on('click', '*[data-confirm-form-url]', function() {
   const text = $(this).data('confirm-form-text') || "Вы уверены?";
    const url = $(this).data('confirm-form-url');
    const inputName = $(this).data('confirm-form-input-name') || 'id';
    const inputValue = $(this).data('confirm-form-input-value');
    const formId = 'confirm_form_' + url.split('/').join('_');
   $(body).append(`<div id="${formId}">
       <form action="${url}" data-auto-close-modal="1" class="form  modal-window__form">
          <input type="hidden" name="${inputName}" value="${inputValue}"/>
          <input type="hidden" name="_from_confirm_form" value="1"/>
          <div class="modal-window__text">
            ${text}
          </div>
          <div class="form__bottom">
            <button class="button button--light">ОК</button>
            <a class="button button--light modal-window__close-button">Отмена</a>
            <div class="response response--light"></div>
          </div>
       </form>
   </div>`);
   showModal(`#${formId}`, 'Подтверждение');
});
$(body).on('click', '*[data-show-modal]', function() {
    showModal($(this).data('show-modal'));
});

$(body).on('click', '*[data-modal-form-url]', function() {
    showModalAjax($.get($(this).data('modal-form-url')+'?X-PJAX=true&_pjax=#pjax-container'),  '#modal_form', '.');
});
