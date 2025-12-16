import {createApp, defineAsyncComponent} from 'vue';
import { createPinia } from 'pinia';

//import { BTable } from 'bootstrap-vue'
//import { BPagination } from 'bootstrap-vue'

let app;
const pinia = createPinia();

const registerComponents = (app) => {

    app.component('pagination', defineAsyncComponent(() => {
        return import('laravel-vue-pagination')
    }));
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
        return import('./components/datepicker/components/Datepicker.vue')
    }));
    app.component('records-search', defineAsyncComponent(() => {
        return import('./components/RecordsSearch.vue')
    }));
    app.component('crossposts-editor', defineAsyncComponent(() => {
        return import('./components/CrosspostsEditor.vue')
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
    app.component('upload-from-device', defineAsyncComponent(() => {
        return import('./components/UploadFromDevice.vue')
    }));
    app.component('regional-channels-list', defineAsyncComponent(() => {
        return import('./components/RegionalChannelsList.vue')
    }));
    app.component('crossposts-manager', defineAsyncComponent(() => {
        return import('./components/CrosspostsManager.vue')
    }));
    app.component('channel-select', defineAsyncComponent(() => {
        return import('./components/ChannelSelect.vue')
    }));
    app.component('date-select', defineAsyncComponent(() => {
        return import('./components/DateSelect.vue')
    }));
    app.component('tags-editor', defineAsyncComponent(() => {
        return import('./components/TagsEditor.vue')
    }));
    app.component('article-bindings-editor', defineAsyncComponent(() => {
        return import('./components/ArticleBindingsEditor.vue')
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
}

export const initializeVue = () => {
    app = createApp({
        template: document.getElementById('app').innerHTML
    });
    app.use(pinia);
    app.use(registerComponents);
    app.mount('#app');
}

export const unmountVue = () => {
    app.unmount()
}



