import {createApp, defineAsyncComponent} from 'vue';
import { createPinia } from 'pinia';
import { App } from "@vue/runtime-core";

//import { BTable } from 'bootstrap-vue'
//import { BPagination } from 'bootstrap-vue'

let app: App;
const pinia = createPinia();

const registerComponents = (app) => {

    app.component('select2', defineAsyncComponent(() => {
        return import('./components/Select2.vue')
    }));

    app.component('names-history-editor', defineAsyncComponent(() => {
        return import('./components/NamesHistoryEditor.vue')
    }));
    app.component('picture-uploader', defineAsyncComponent(() => {
        return import('./components/PictureUploader.vue')
    }));
    app.component('record-form', defineAsyncComponent(() => {
        return import('./components/RecordForm.vue')
    }));
    app.component('user-groups-select', defineAsyncComponent(() => {
        return import('./components/UserGroupsSelect.vue')
    }));
    app.component('datepicker', defineAsyncComponent(() => {
        return import('./components/Datepicker.vue')
    }));
    app.component('records-search', defineAsyncComponent(() => {
        return import('./components/RecordsSearch.vue')
    }));
    app.component('crossposts-editor', defineAsyncComponent(() => {
        return import('./components/crossposts/CrosspostsEditor.vue')
    }));
    app.component('questionnaire-editor', defineAsyncComponent(() => {
        return import('./components/QuestionnaireEditor.vue')
    }));
    app.component('records-list-picker', defineAsyncComponent(() => {
        return import('./components/RecordsListPicker.vue')
    }));
    app.component('video-cutter', defineAsyncComponent(() => {
        return import('./components/VideoCutter.vue')
    }));
    app.component('history-event-editor', defineAsyncComponent(() => {
        return import('./components/HistoryEventEditor.vue')
    }));
    app.component('additional-channels-editor', defineAsyncComponent(() => {
        return import('./components/AdditionalChannelsEditor.vue')
    }));
    app.component('mass-uploader', defineAsyncComponent(() => {
        return import('./components/MassUploader.vue')
    }));

    app.component('regional-channels-list', defineAsyncComponent(() => {
        return import('./components/RegionalChannelsList.vue')
    }));
    app.component('crossposts-manager', defineAsyncComponent(() => {
        return import('./components/crossposts/CrosspostsManager.vue')
    }));
    app.component('channel-select', defineAsyncComponent(() => {
        return import('./components/record-form/ChannelSelect.vue')
    }));
    app.component('type-select', defineAsyncComponent(() => {
        return import('./components/record-form/TypeSelect.vue')
    }));
    app.component('date-select', defineAsyncComponent(() => {
        return import('./components/DateSelect.vue')
    }));
    app.component('response', defineAsyncComponent(() => {
        return import('./components/Response.vue')
    }));
    app.component('preloader', defineAsyncComponent(() => {
        return import('./components/Preloader.vue')
    }));
    app.component('tiptap-editor', defineAsyncComponent(() => {
        return import('./components/TiptapEditor.vue')
    }));
    app.component('tags-editor', defineAsyncComponent(() => {
        return import('./components/TagsEditor.vue')
    }));
    app.component('article-bindings-editor', defineAsyncComponent(() => {
        return import('./components/ArticleBindingsEditor.vue')
    }));

    app.component('commercials-info-editor', defineAsyncComponent(() => {
        return import('./components/records-manager/CommercialsInfoEditor.vue')
    }));
    app.component('channel-and-program-transfer', defineAsyncComponent(() => {
        return import('./components/records-manager/ChannelAndProgramTransfer.vue')
    }));
    app.component('type-and-category-transfer', defineAsyncComponent(() => {
        return import('./components/records-manager/TypeAndCategoryTransfer.vue')
    }));

    app.component('commercials-panel', defineAsyncComponent(() => {
        return import('./components/admin/CommercialsPanel.vue')
    }));
    app.component('permissions-manager', defineAsyncComponent(() => {
        return import('./components/admin/PermissionsManager.vue')
    }));
    app.component('channels-manager', defineAsyncComponent(() => {
        return import('./components/admin/ChannelsManager.vue')
    }));
    app.component('channels-order-manager', defineAsyncComponent(() => {
        return import('./components/admin/ChannelsOrderManager.vue')
    }));
    app.component('smiles-manager', defineAsyncComponent(() => {
        return import('./components/admin/SmilesManager.vue')
    }));
    app.component('users-manager', defineAsyncComponent(() => {
        return import('./components/admin/UsersManager.vue')
    }));
    app.component('categories-manager', defineAsyncComponent(() => {
        return import('./components/admin/CategoriesManager.vue')
    }));
    app.component('programs-manager', defineAsyncComponent(() => {
        return import('./components/admin/ProgramsManager.vue')
    }));
    app.component('programs-order-manager', defineAsyncComponent(() => {
        return import('./components/admin/ProgramsOrderManager.vue')
    }));
}

export const initializeVue = (selector = '#app') => {
    const el = document.querySelector(selector);
    //app?.unmount()
    app = createApp({
        template: el.innerHTML
    });
    app.use(pinia);
    app.use(registerComponents);
    app.mount(el);
}




