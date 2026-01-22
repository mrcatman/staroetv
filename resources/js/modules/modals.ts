interface ModalData {
    name: string,
    onClose: () => void,
    params?: ModalParams
}

declare global {
    interface Window {
        openedModals: ModalData[];

        showModal: (selector: string, title?: string, onClose?: () => void, params?: ModalParams) => void
        closeModal: (modal: JQuery<HTMLElement>) => void
    }
}

const openedModals: ModalData[] = [];

window.openedModals = openedModals;

const body = $('body');
const width = 800;

export interface ModalParams {
    backdrop?: boolean
}

const showModal = (selector: string, title: string = null, onClose: () => void = null, params?: ModalParams) => {
    const name = selector.substring(1);
    openedModals.push({
        name,
        onClose,
        params
    });

    title = title || $(selector).data('title') || "";


    const existingModal = $(`.modal-window[data-name="${name}"]`);
    if (!$(existingModal).length) {
        $(body).append(`
            <div class="modal-window" data-name="${name}" data-selector="${selector}">
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

        const modal = $(`.modal-window[data-name="${name}"]`);

        $('.modal-window').removeClass('modal-window--top');
        $(modal).addClass('modal-window--top');

        const modalInner = $(modal).find('.modal-window__inner');
        const modalContent = $(modal).find('.modal-window__content');
        //let width = $(elementName).width()  > 800 ? 800 :  $(elementName).width();
        // let height = $(elementName).height() > 600 ? 600 :  $(elementName).height();

        const windowWidth = $(window).width();

        if (!$(selector).length) {
            $(body).append(`<div id="${name}" style="display:none"></div>`);
        }
        $(selector).show().appendTo(modalContent);

        const modalWidth = Math.min(width, $(window).width() - 16);
        $(modal).css({
            width: `${modalWidth}px`,
            left: `${(windowWidth - modalWidth) / 2}px`,
        });
        centerY(modal);

        $(modal).draggable({ cancel: '.modal-window__content, .modal-window__content *' });
        $(modalInner).resizable();

        if (params?.backdrop && !$('.modal-backdrop').length) {
            $(body).append('<div class="modal-backdrop"></div>');
        }
        return modal;
    }
    return existingModal;
}


function showModalAjax(ajaxCall: JQuery.jqXHR, selector: string, title: string = null, onClose = null) {
    showModal(selector, title, onClose);
    const modal = $(`.modal-window[data-selector='${selector}']`);
    let content = $(modal).find('.modal-window__content');
    $(content).html('<div class="modal-window__preloader-container"><img class="modal-window__preloader" src="/img/ajax.gif"></div>');
    ajaxCall.done((res) => {
        if (res.data) {
            $(content).html(res.data.html);
            if (res.data.title) {
                $(content).parents('.modal-window').find('.modal-window__title').html(res.data.title);
            }
            centerY(modal);
        } else {
            $(content).html(res);
        }
    }).fail((e) => {
        const message = e.responseJSON?.message || `Ошибка ${e.status}, повторите попытку позже`;
        $(content).html(`<div class="modal-window__form"><div class="response response--light response--error">${message}</div></div>`);
    });
}

$(body).on('dragstart', '.modal-window', function() {
    $('.modal-window').removeClass('modal-window--top');
    $(this).addClass('modal-window--top');
});

const closeModal = (element: JQuery<HTMLElement>) => {
    const selector = $(element).data('selector');
    const name = $(element).data('name');

    const modal = openedModals.find(modal => modal.name === name);

    openedModals.splice(openedModals.map(m => m.name).indexOf(modal.name), 1);
    $(selector).hide().appendTo(body);
    $(element).remove();

    if (openedModals.filter(modal => modal.params?.backdrop)) {
        $('.modal-backdrop').remove();
    }

    modal.onClose && modal.onClose();
}

$(body).on('click', '.modal-window__close, .modal-window__close-button', function() {
    closeModal($(this).parents('.modal-window'));
});

const centerY = (modal: JQuery<HTMLElement>) => {
    const windowHeight = $(window).height();
    let totalHeight = 0;
    $(modal).find('.modal-window__content').children().each(function(){
        totalHeight += $(this).outerHeight(true);
    });
    const maxHeight = windowHeight / 4 * 3;
    const height = totalHeight > maxHeight ? maxHeight : totalHeight;
    $(modal).css({
        top: `${(windowHeight - height) / 2}px`,
    });
}

window.showModal = showModal;
window.closeModal = closeModal;

export {
    showModal,
    showModalAjax,
    closeModal
};
