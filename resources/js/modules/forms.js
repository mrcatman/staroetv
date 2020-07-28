import replaceDom from './replaceDom';
import {showModal, showModalAjax} from "./modals";

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
    let checkboxesData = {};
    $(this).find('input[type="checkbox"]').each(function() {
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

    $(this).append('<div class="form__preloader"><img src="/pictures/ajax.gif"></div>');
    let formData = {};
    data.forEach(item => {
        formData[item.name] = item.value;
    });

    $(this).find('.input-container').removeClass('input-container--with-errors');
    $(this).find('.input-container__message').html('');
    let response = $(this).find('.response');

    let confirmed = true;
    if ($(this).data('confirm')) {
        let text = $(this).data('confirm-text') || "Вы уверены?";
        if (!confirm(text)) {
            confirmed = false;
        }
    }
    if (confirmed) {
        $.ajax(url, {
            data: JSON.stringify(formData),
            contentType: 'application/json',
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
                    $(response)[0].scrollIntoView();
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
   let text = $(this).data('confirm-form-text') || "Вы уверены?";
  let url = $(this).data('confirm-form-url');
   let inputName = $(this).data('confirm-form-input-name');
   let inputValue = $(this).data('confirm-form-input-value');
   let formId = 'confirm_form_' + url.split('/').join('_');
   $(body).append(`<div id="${formId}">
       <form action="${url}" data-auto-close-modal="1" class="form  modal-window__form">
          <input type="hidden" name="${inputName}" value="${inputValue}"/>
          <input type="hidden" name="_from_confirm_form" value="1"/>
          <div class="modal-window__small-text">
            ${text}
          </div>
          <div class="form__bottom">
            <button class="button button--light">ОК</button> 
            <a class="button button--light modal-window__close-button">Отмена</a> 
            <div class="response response--light"></div>
          </div>
       </form>
   </div>`);
   showModal('#' + formId, 'Подтверждение');
});
$(body).on('click', '*[data-show-modal]', function() {
    showModal($(this).data('show-modal'));
});

$(body).on('click', '*[data-modal-form-url]', function() {
    showModalAjax($.get($(this).data('modal-form-url')+'?X-PJAX=true&_pjax=#pjax-container'),  '#modal_form', '.');
});
