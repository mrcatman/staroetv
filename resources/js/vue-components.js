import { BTable } from 'bootstrap-vue'
import { BPagination } from 'bootstrap-vue'
Vue.component('b-table', BTable)
Vue.component('b-pagination', BPagination)
Vue.component('select2', require('./components/Select2.vue').default);

Vue.component('names-history-editor', require('./components/NamesHistoryEditor.vue').default);
Vue.component('picture-uploader', require('./components/PictureUploader.vue').default);
Vue.component('record-form', require('./components/RecordForm.vue').default);
Vue.component('user-groups-select', require('./components/UserGroupsSelect.vue').default);
Vue.component('datepicker', require('./components/datepicker/components/Datepicker').default);
Vue.component('pagination', require('laravel-vue-pagination'));
Vue.component('records-search', require('./components/RecordsSearch').default);
Vue.component('crossposts-editor', require('./components/CrosspostsEditor').default);
Vue.component('questionnaire-editor', require('./components/QuestionnaireEditor').default);
Vue.component('records-list-picker', require('./components/RecordsListPicker.vue').default);

Vue.component('permissions-manager', require('./components/admin/PermissionsManager.vue').default);
Vue.component('channels-manager', require('./components/admin/ChannelsManager.vue').default);
Vue.component('channels-order-manager', require('./components/admin/ChannelsOrderManager.vue').default);
Vue.component('smiles-manager', require('./components/admin/SmilesManager.vue').default);
Vue.component('users-manager', require('./components/admin/UsersManager.vue').default);
Vue.component('program-categories-manager', require('./components/admin/ProgramCategoriesManager.vue').default);
Vue.component('programs-manager', require('./components/admin/ProgramsManager.vue').default);


