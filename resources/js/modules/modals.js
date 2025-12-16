window.openedModals = [];
window.callbacksOnCloseModals = {};

const body = $('body');
const width = 800;
const height = 600;

const showModal = (selector, title = null, onClose = null) => {
    let modalName = selector.substring(1);
    openedModals.push(modalName);
    if (onClose !== undefined) {
        callbacksOnCloseModals[modalName] = onClose;
    }
    title = title || $(selector).data('title') || "";

    const existingModal = $(`.modal-window[data-name="${modalName}"]`);
    if (!$(existingModal).length) {
        $(body).append(`
            <div class="modal-window" data-name="${modalName}" data-selector="${selector}">
                    <div class="modal-window__inner">
                        <div class="modal-window__top">
                            <div class="modal-window__title">${title}</div>
                            <div class="modal-window__close">
                                <i class="fa fa-times"></i>
                            </div>
                         </div>
                        <div class="modal-window__content"></div>
                     </div>
                </div>`
        );

        const modal = $(`.modal-window[data-name="${modalName}"]`);

        $('.modal-window').removeClass('modal-window--top');
        $(modal).addClass('modal-window--top');

        const modalInner = $(modal).find('.modal-window__inner');
        const modalContent = $(modal).find('.modal-window__content');
        //let width = $(elementName).width()  > 800 ? 800 :  $(elementName).width();
        // let height = $(elementName).height() > 600 ? 600 :  $(elementName).height();

        const windowWidth = $(window).width();
        const windowHeight = $(window).height();
        if (!$(selector).length) {
            $(body).append('<div id="'+modalName+'" style="display:none"></div>')
        }
        $(selector).show().appendTo(modalContent);
        $(modal).css('width',  width + 'px');
        $(modal).css('left', ((windowWidth - width) / 2) + 'px');
        $(modal).css('top', ((windowHeight - height) / 2) + 'px');
        $(modal).draggable({ cancel: '.modal-window__content, .modal-window__content *' });
        $(modalInner).resizable();

        return modal;
    }
    return existingModal;
}


function showModalAjax(fn, elementName, title = null, onClose = null) {
    showModal(elementName, title, onClose);
    let content = $(".modal-window[data-selector='"+elementName+"']").find('.modal-window__content');
    $(content).html('<div class="modal-window__preloader-container"><img class="modal-window__preloader" src="/img/ajax.gif"></div>');
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
}

$(body).on('dragstart', '.modal-window', function() {
    $('.modal-window').removeClass('modal-window--top');
    $(this).addClass('modal-window--top');
});

const closeModal = (modal) => {
    const selector = $(modal).data('selector');
    const modalName = $(modal).data('name');
    openedModals.splice(openedModals.indexOf(modalName), 1);
    $(selector).hide().appendTo(body);
    $(modal).remove();
    if (callbacksOnCloseModals[modalName] !== undefined && callbacksOnCloseModals[modalName] !== null) {
        callbacksOnCloseModals[modalName]();
    }
}

$(body).on('click', '.modal-window__close, .modal-window__close-button', function() {
    const modal = $(this).parents('.modal-window');
    closeModal(modal);
});

window.showModal = showModal;
window.closeModal = closeModal;

export {
    showModal,
    showModalAjax,
    closeModal
};
