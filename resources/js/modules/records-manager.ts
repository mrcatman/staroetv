import { replaceHTML } from './replace-html';
import { showModalAjax } from './modals';
import { initializeVue } from '../vue-components';
import { PRELOADER_CLASS, PRELOADER_HTML } from './preloader';
const body = $('body');

let canEditRecords = null;
let menuItemIds = [];
let singleMenu = false;

$(body).on('click', '.record-item,.radio-recording', async (e) => {
    if (!e.ctrlKey && !e.shiftKey) {
        return;
    }
    if (canEditRecords === false) {
        return;
    }

    e.preventDefault();

    if (canEditRecords === null) {
        canEditRecords = (await $.get(route('profile.permissions'))).can_edit_records;
    }
    if (canEditRecords === false) {
        return;
    }

    const id = $(e.currentTarget).data('id');
    if (!id) {
        return;
    }

    if (e.ctrlKey) {
        $(body).off('click', detectClickOutside);
        removeMenu();

        setTimeout(() => {
            menuItemIds = [id];
            singleMenu = true;

            showMenu(e);
        }, 1);

    } else if (e.shiftKey) {
        $(e.currentTarget).toggleClass('selected');
        const selected = $(e.currentTarget).hasClass('selected');

        if (singleMenu) {
            singleMenu = false;
            menuItemIds = [];
        }
        selected ? menuItemIds.push(id) : menuItemIds = menuItemIds.filter(i => i !== id);
        menuItemIds = menuItemIds.sort();

        const actions = $('.actions');
        if (!menuItemIds.length) {
            $(actions).remove();
            return;
        }

        if (!actions.length) {
            $(body).append(`<div class="actions">
                <span class="actions__text">Выбрано записей: <span class="actions__number">${menuItemIds.length}</span></span>
                <a class="button actions__button">Действия</a>
                <a class="button button--light actions__clear">Отмена</a>
            </div>`);
        } else {
            $(actions).find('.actions__number').text(menuItemIds.length);
        }
    }

});

$(body).on('click', '.menu--records .menu__item', async (e) => {
    e.preventDefault();
    if ($(e.target).data('instant')) {
        const menu = $(e.target).parents('.menu');
        $(menu).append(PRELOADER_HTML);

        try {
            const { data } = await $.post($(e.currentTarget).data('url'), {ids: menuItemIds});
            if (data?.html) {
                replaceHTML(data.html);
            }

            removeMenu();
        } catch (e) {
            alert(e.responseJSON.message ?? e.message ?? 'Ошибка, попробуйте позже');
        } finally {
            $(menu).find(`.${PRELOADER_CLASS}`).remove();
        }

        return;
    }
    removeMenu();

    const selector = `records_manager_${menuItemIds.join(',')}`;
    await showModalAjax($.get($(e.currentTarget).data('url'), {ids: menuItemIds}), `#${selector}`);
    initializeVue(`.modal-window[data-name="${selector}"]`);
});

const showMenu = async(e: JQuery.ClickEvent) => {
    $(body).append(`<div class="menu menu--records" data-ids="${menuItemIds.join(',')}">${PRELOADER_HTML}</div>`);
    const menu = $(body).find('.menu');
    const item = $(e.target).parents('.actions,.record-item,.radio-recording');
    $(menu).css({
        top: $(item).offset().top + e.offsetY,
        left: $(item).offset().left + e.offsetX,
    })
    const {data} = await $.get(route('records.edit.menu', {
        ids: menuItemIds
    }));
    replaceHTML(data.html);
    $(body).on('click', detectClickOutside);
}
const detectClickOutside = (e) => {
    const menu = $(`.menu[data-ids="${menuItemIds.join(',')}"]`);
    if (menu.length === 0) {
        return;
    }
    if (!$(menu).is(e.target) && $(menu).has(e.target).length === 0) {
        removeMenu(menu);
    }
}

const removeMenu = (menu?: JQuery<HTMLElement>) => {
    if (!menu) {
        menu = $(`.menu[data-ids="${menuItemIds.join(',')}"]`);
    }

    $(menu).remove();
    $(body).off('click', detectClickOutside);
}

$(body).on('click', '.actions__button', e => showMenu(e));
$(body).on('click', '.actions__clear', e => {
    menuItemIds = [];
    $('.actions').remove();
    $('.selected').removeClass('selected');
});

const highlightSelected = () => {
    menuItemIds.forEach(id => {
        $(`.record-item[data-id=${id}], .radio-recording[data-id=${id}]`).addClass('selected');
    });
}

window.execOnMounted.push(highlightSelected);
window.execOnRecordsPageChange.push(highlightSelected);
