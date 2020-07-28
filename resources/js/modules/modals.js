window.openedModals = [];
window.callbacksOnCloseModals = {};
let body = $('body');

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
        if ($(elementName).length === 0) {
            $(body).append('<div id="'+modalName+'" style="display:none"></div>')
        }
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
        if (res.data) {
            $(content).html(res.data.html);
            if (res.data.title) {
                $(content).parents('.modal-window').find('.modal-window__title').html(res.data.title);
            }
        } else {
            $(content).html(res);
        }
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

module.exports = {
    showModal,
    showModalAjax
};
