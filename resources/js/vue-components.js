import { BTable } from 'bootstrap-vue'
import { BPagination } from 'bootstrap-vue'
Vue.component('b-table', BTable)
Vue.component('b-pagination', BPagination)
Vue.component('select2', require('./components/Select2').default);

Vue.component('names-history-editor', require('./components/NamesHistoryEditor').default);
Vue.component('picture-uploader', require('./components/PictureUploader').default);
Vue.component('record-form', require('./components/RecordForm').default);
Vue.component('user-groups-select', require('./components/UserGroupsSelect').default);
Vue.component('datepicker', require('./components/datepicker/components/Datepicker').default);
Vue.component('pagination', require('laravel-vue-pagination'));
Vue.component('records-search', require('./components/RecordsSearch').default);
Vue.component('crossposts-editor', require('./components/CrosspostsEditor').default);
Vue.component('questionnaire-editor', require('./components/QuestionnaireEditor').default);
Vue.component('records-list-picker', require('./components/RecordsListPicker').default);
Vue.component('video-cutter', require('./components/VideoCutter').default);
Vue.component('history-event-editor', require('./components/HistoryEventEditor').default);
Vue.component('additional-channels-editor', require('./components/AdditionalChannelsEditor').default);
Vue.component('mass-uploader', require('./components/MassUploader').default);
Vue.component('upload-from-device', require('./components/UploadFromDevice.vue').default);
Vue.component('regional-channels-list', require('./components/RegionalChannelsList').default);
Vue.component('crossposts-manager', require('./components/CrosspostsManager').default);

Vue.component('permissions-manager', require('./components/admin/PermissionsManager').default);
Vue.component('channels-manager', require('./components/admin/ChannelsManager').default);
Vue.component('channels-order-manager', require('./components/admin/ChannelsOrderManager').default);
Vue.component('smiles-manager', require('./components/admin/SmilesManager').default);
Vue.component('users-manager', require('./components/admin/UsersManager').default);
Vue.component('categories-manager', require('./components/admin/CategoriesManager').default);
Vue.component('programs-manager', require('./components/admin/ProgramsManager').default);

Vue.component('tags-editor', require('./components/TagsEditor').default);
Vue.component('article-bindings-editor', require('./components/ArticleBindingsEditor').default);
