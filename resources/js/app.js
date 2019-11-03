
window.$ = require('jquery');
window.jQuery = window.$;
require('jquery-pjax');
require('jquery-ui-bundle');

window.Vue = require('vue');
require ('./vue-components');
require ('./bbcodes');
require ('./uVideoPlayer');
let translit = require ('./translit');
import 'select2';
import 'select2/dist/css/select2.css';

let onReady = () => {
    let body = $('body');

    // FORMS
    $(".select").select2();
    jQuery.each( [ "put", "delete" ], function( i, method ) {
        jQuery[ method ] = function( url, data, callback, type ) {
            if ( jQuery.isFunction( data ) ) {
                type = type || callback;
                callback = data;
                data = undefined;
            }

            return jQuery.ajax({
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
                $(this).find('.response')[0].scrollIntoView();
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
                $(this).find('.response')[0].scrollIntoView();
            });
    });

    $(body).on('change', '.input-container--checkbox--toggle input[type="checkbox"]', function(e) {
        if ($(this).is(':checked')) {
            $(this).parents('.input-container--checkbox--toggle').find('.input').attr('disabled', false);
        } else {
            $(this).parents('.input-container--checkbox--toggle').find('.input').attr('disabled', true);
        }
    });

    // COMMENTS

    $(body).on('click', '.comments__pages .page-link', function(e) {
        e.preventDefault();
        const page = $(this).attr('href').split('?page=')[1];
        const conditions = $(this).parents('.comments').data('conditions');
        $(this).parents('.comments').append('<div class="comments__preloader"></div>');
        $.post('/comments/ajax', {page, conditions})
            .done((res) => {
                $(this).parents('.comments').find('.comments__preloader').remove();
                $(this).parents('.comments').html(res.data.html);
            })
    });

    $(body).on('click', '.comment__reply', function() {
        let id = $(this).parents('.comment').data('id');
       $('#reply_form_container').html($('.comments > .comments__form').html());
       $('#reply_form_container').find('input[name="parent_id"]').val(id);
       $('#reply_form_container').find('#message').attr('id', 'message_modal');
       $('.comments > .comments__form').append('<div class="comments__form__disable"></div>')
       showModal('#reply_form_container', 'Ответить на комментарий', () => {
           bb.init('message');
           $('.comments__form__disable').remove();
       });
       bb.init('message_modal');
    });


    $(body).on('click', '.comment__edit', function() {
        let id = $(this).parents('.comment').eq(0).data('id');
        $('#edit_form_container').html($('.comments > .comments__form').html());
        $('#edit_form_container').find('input[name="parent_id"]').val(id);
        $('#edit_form_container').find('textarea[name="message"]').attr('id', 'message_edit_modal');
        $('#edit_form_container').find('textarea[name="message"]').val($(this).parents('.comment').eq(0).find('.comment__original-text').html().trim());
        $('#edit_form_container').find('form').attr('action', '/comments/edit');
        $('#edit_form_container').find('input[name="id"]').val(id);
        $('.comments > .comments__form').append('<div class="comments__form__disable"></div>')
        showModal('#edit_form_container', 'Редактировать комментарий', () => {
            bb.init('message');
            $('.comments__form__disable').remove();
        });
        bb.init('message_edit_modal');
    });

    $(body).on('click', '.comment__delete', function() {
        let id = $(this).parents('.comment').eq(0).data('id');
        if (confirm("Вы уверены, что хотите удалить комментарий?")) {
            $.post('/comments/delete', {id}).done(res => {
                if (res.status) {
                    replaceDom(res.data.dom);
                } else {
                    alert(res.text);
                }
            })
        }
    });

    $(body).on('click', '.comment__rating__button', function() {
        let comment_id = $(this).parents('.comment').eq(0).data('id');
        let weight = !$(this).hasClass('comment__rating__button--minus') ? 1 : -1;
        $.post('/comments/rating', {comment_id, weight}).done(res => {
            if (res.status) {
                replaceDom(res.data.dom);
            } else {
                alert(res.text);
            }
        })
    });

    // PJAX RELATED

    $(document).pjax('a[target!="_blank"]', '#pjax-container', {timeout: 10000});
    onPageChange();
    function onPageChange() {
        let pathname = window.location.pathname;
        $('a').removeClass('link--active');
        $('a[href="'+pathname+'"]').addClass('link--active');
        if ($('.user-page__info-container').length > 0) {
            if ($('.user-page__avatar').length > 0) {
                let height =  $('.user-page__info-container').height();
                $('.user-page__avatar').css('height', height + 'px');
                let img = new Image();
                img.src = $('.user-page__avatar').attr('src');
                img.onload = () => {
                    $('.user-page__info-container').hide();
                    $('.user-page__info-container').css('flex', 'auto').css('width', 'calc(100% - '+($('.user-page__avatar').width())+'px)')
                    $('.user-page__info-container').show();
                };
            } else {

            }
        }
        $('.comment__video-player, .forum-message__video-player').each(function() {
            let params = $(this).data('params');
            params = params.replace(/'/g, '"');
            params = JSON.parse(params);
            _uVideoPlayer(params, $(this).data('element'));
        })
        const app = new Vue({
            el: '#app',
            mounted: () => {
                if ($('#editor').length > 0){
                    CKEDITOR.replace('editor');
                }
            }
        });
    }

    $(document).on('pjax:success', () => {
        onPageChange();
    });


    // MODALS

    let openedModals = [];
    let callbacksOnCloseModals = {};


    function showModal(elementName, title = null, onClose = null) {
        let modalName = elementName.substring(1);
        openedModals.push(modalName);
        if (onClose !== undefined) {
            callbacksOnCloseModals[modalName] = onClose;
        }
        title = title || $(elementName).data('title') || "";
        if ($('.modal-window[data-name="'+modalName+'"]').length === 0) {
            $(body).append('<div class="modal-window" data-name="'+modalName+'" data-selector="'+elementName+'"><div class="modal-window__inner">' +
                '<div class="modal-window__top"><div class="modal-window__title">'+title+'</div><div class="modal-window__close">x</div></div>' +
                '<div class="modal-window__content"></div>' +
                '</div></div>');
            let modal = $('.modal-window[data-name="'+modalName+'"]');
            $('.modal-window').removeClass('modal-window--top');
            $(modal).addClass('modal-window--top');
            let modalInner = $('.modal-window[data-name="'+modalName+'"] .modal-window__inner');
            let modalContent = $('.modal-window[data-name="'+modalName+'"] .modal-window__content');
            //let width = $(elementName).width()  > 800 ? 800 :  $(elementName).width();
           // let height = $(elementName).height() > 600 ? 600 :  $(elementName).height();
            let width = 800;
            let height = 600;
            let windowWidth = $(window).width();
            let windowHeight = $(window).height();
            $(elementName).show().appendTo(modalContent);
            $(modal).css('width',  width + 'px');
            $(modal).css('left', ((windowWidth - width) / 2) + 'px');
            $(modal).css('top', ((windowHeight - height) / 2) + 'px');
            $(modal).draggable();
            $(modalInner).resizable();
        }
    }

    function showModalAjax(fn, elementName, title = null, onClose = null) {
        showModal(elementName, title, onClose);
        let content = $(".modal-window[data-selector='"+elementName+"']").find('.modal-window__content');
        $(content).html('<div class="modal-window__preloader-container"><img class="modal-window__preloader" src="/pictures/ajax.gif"></div>');
        fn.done((res) => {
            $(content).html(res.data.html);
        });
    };



    $(body).on('dragstart', '.modal-window', function() {
        $('.modal-window').removeClass('modal-window--top');
        $(this).addClass('modal-window--top');
    });

    $(body).on('click', '.modal-window__close, .modal-window__close-button', function() {
        let modal = $(this).parents('.modal-window');
        if ($(modal).hasClass('modal-window--vue')) return;
        let selectorName = $(modal).data('selector');
        let modalName = $(modal).data('name');
        openedModals.splice(openedModals.indexOf(modalName), 1);
        $(selectorName).hide().appendTo(body);
        $(modal).remove();
        if (callbacksOnCloseModals[modalName] !== undefined && callbacksOnCloseModals[modalName] !== null) {
            callbacksOnCloseModals[modalName]();
        }
    });

    // MODAL TRIGGERS

    $(body).on('click', '.user-page__info-block__value--reputation', function() {
        showModal('#reputation_history');
    });
    $(body).on('click', '.user-page__info-block__value--warnings', function() {
        showModal('#warnings_history');
    });
    $(body).on('click', '.user-page__info-block__value--awards', function() {
        showModal('#awards_history');
    });
    $(body).on('click', '.user-page__info-block__change--reputation', function() {
        showModal('#change_reputation');
        $('#change_reputation input[name="user_id"]').val($(this).data('user-id'));
    });

    $(body).on('click', '.button--login', function(e) {
        showModal('#login');
        e.preventDefault();
    });

    $(body).on('click', '.form__bottom__link', function(e) {
        $(this).parents('.modal-window').find('.modal-window__close').click();
    });

    // FORUM

    $(body).on('click', '.bb-editor__smiles__all', function() {
        showModal('#all_smiles');
    });

    $(body).on('click', '.forum-section__delete-topic', function() {
        showModal('#delete_topic');
    });


    $(body).on('click', '.forum-section__move-topic', function() {
        showModal('#move_topic');
    });

    $(body).on('click', '.forum-message__reputation__number', function() {
        let user_id = $(this).data('user-id');
        showModalAjax($.post('/reputation/ajax', {user_id}), '#reputation_history_' + user_id);
    });
    $(body).on('click', '.forum-message__reputation__change', function() {
        let user_id = $(this).data('user-id');
        let message_id = $(this).parents('.forum-message').attr('id');
        showModal('#change_reputation');
        $('#change_reputation input[name="user_id"]').val(user_id);
        $('#change_reputation input[name="forum_message_id"]').val(message_id);
    });
    $(body).on('click', '.forum-message__awards__number', function() {
        let user_id = $(this).data('user-id');
        showModalAjax($.post('/awards/ajax', {user_id}), '#awards_history_' + user_id);
    });
    $(body).on('click', '.forum-message__warnings__number', function() {
        let user_id = $(this).data('user-id');
        showModalAjax($.post('/warnings/ajax', {user_id}), '#warnings_history_' + user_id);
    });



    $(body).on('click', '.forum-message__edit', function() {
        let message = $(this).parents('.forum-message');
        let message_id = $(message).data('id');
        $.post('/forum/get-edit-form', {message_id}).done(res => {
            $(message).find('.forum-message__content').hide();
            $(message).find('.forum-message__edit-form').show().html(res.data.html);
        });
    });

    $(body).on('click', '.forum-message__delete', function() {
        let message = $(this).parents('.forum-message');
        let message_id = $(message).data('id');
        if (confirm("Вы уверены, что хотите удалить это сообщение?")) {
            $.post('/forum/delete-message', {message_id}).done(res => {
                if (res.status) {
                    replaceDom(res.data.dom);
                } else {
                    alert(res.text);
                }
            })
        }
    });

    $(body).on('click', '.forum-message .bb-editor__cancel', function() {
        let message = $(this).parents('.forum-message');
        $(message).find('.forum-message__content').show();
        $(message).find('.forum-message__edit-form').hide();
    });

    $(body).on('change', '#forum_state', function() {
        if ($(this).val() == "4") {
            $('#forum_move_to').show();
        } else {
            $('#forum_move_to').hide();
        }
    })

    function forumMessageCallback(res) {
        if (res.status) {
            let currentPage = parseInt($('.pagination').eq(0).find('.page-item.active .page-link').text().trim());
            let lastPage = parseInt(res.data.last_page);
            if (currentPage !== lastPage) {
                let topic_id = $('.forum-section').data('topic-id');
                let forum_id = $('.forum-section').data('forum-id');
                let url = "/forum/"+forum_id+"-"+topic_id+"-"+lastPage+"#"+res.data.message.id;
                $.pjax({url: url, container: '#pjax-container'})
            } else {
                replaceDom(res.data._dom);
            }
        }
    }
    window.forumMessageCallback = forumMessageCallback;

    // TABS
    $(body).on('click', '.tab', function() {
        let id = $(this).parents('.tabs').data('id');
        let tab = $(this).data('content');
        $(this).parents('.tabs').find('.tab').removeClass('tab--active');
        $(this).addClass('tab--active');
        $(body).find('.tab-content[data-id="'+id+'"]').hide();
        $(body).find('.tab-content[data-id="'+id+'"][data-tab="'+tab+'"]').show();
    })

    //CHANNELS

    $(body).on('change', '#channel_name', function() {
        let name = $(this).val();
        let transliterated = translit(name);
        $('#channel_url').val(transliterated);
    });

    $(body).on('click', '.cities-list__item', function() {
        let city = $(this).data('city');
        $('.cities-list__item').removeClass('cities-list__item--active');
        $(this).addClass('cities-list__item--active');
        if ($(this).hasClass('cities-list__item--all')) {
            $(this).parents('.tab-content').find('.channel-item').show();
        } else {
            $(this).parents('.tab-content').find('.channel-item').hide();
            $(this).parents('.tab-content').find('.channel-item[data-city="'+city+'"]').show();
        }
    })


    //PAGES
    $(body).on('click', '.button--delete-page', function() {
        if ($(this).data('id')) {
            $('input[name="page_id"]').val($(this).data('id'));
        }
        showModal('#delete_page');
    });
};
$(document).ready(function() {
    onReady();
});