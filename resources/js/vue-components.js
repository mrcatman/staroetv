import Vue from 'vue';

//import { BTable } from 'bootstrap-vue'
//import { BPagination } from 'bootstrap-vue'
import LaravelVuePagination from 'laravel-vue-pagination'

import Select2 from './components/Select2.vue'
import NamesHistoryEditor from './components/NamesHistoryEditor.vue';
import PictureUploader from './components/PictureUploader.vue';
import RecordForm from './components/RecordForm.vue';
import UserGroupsSelect from './components/UserGroupsSelect.vue';
import Datepicker from './components/datepicker/components/Datepicker.vue';
import RecordsSearch from './components/RecordsSearch.vue';
import CrosspostsEditor from './components/CrosspostsEditor.vue';
import QuestionnaireEditor from './components/QuestionnaireEditor.vue';
import RecordsListPicker from './components/RecordsListPicker.vue';
import VideoCutter from './components/VideoCutter.vue';
//import HistoryEventEditor from './components/HistoryEventEditor.vue';
import AdditionalChannelsEditor from './components/AdditionalChannelsEditor.vue';
import MassUploader from './components/MassUploader.vue';
import UploadFromDevice from './components/UploadFromDevice.vue';
import RegionalChannelsList from './components/RegionalChannelsList.vue';
import CrosspostsManager from './components/CrosspostsManager.vue';

import PermissionsManager from './components/admin/PermissionsManager.vue';
//import ChannelsManager from './components/admin/ChannelsManager.vue';
import ChannelsOrderManager from './components/admin/ChannelsOrderManager.vue';
import SmilesManager from './components/admin/SmilesManager.vue';
//import UsersManager from './components/admin/UsersManager.vue';
import CategoriesManager from './components/admin/CategoriesManager.vue';
import ProgramsManager from './components/admin/ProgramsManager.vue';

import TagsEditor from './components/TagsEditor.vue';
import ArticleBindingsEditor from './components/ArticleBindingsEditor.vue';
import ChannelSelect from './components/ChannelSelect.vue';
import DateSelect from './components/DateSelect.vue';

Vue.component('pagination', LaravelVuePagination);
// //Vue.component('b-table', BTable)
// //Vue.component('b-pagination', BPagination)
 Vue.component('select2', Select2);
//
Vue.component('names-history-editor', NamesHistoryEditor);
Vue.component('picture-uploader', PictureUploader);
Vue.component('record-form', RecordForm);
Vue.component('user-groups-select', UserGroupsSelect);
Vue.component('datepicker', Datepicker);
Vue.component('records-search', RecordsSearch);
Vue.component('crossposts-editor', CrosspostsEditor);
Vue.component('questionnaire-editor', QuestionnaireEditor);
Vue.component('records-list-picker', RecordsListPicker);
Vue.component('video-cutter', VideoCutter);
// //Vue.component('history-event-editor', HistoryEventEditor);
Vue.component('additional-channels-editor', AdditionalChannelsEditor);
Vue.component('mass-uploader', MassUploader);
Vue.component('upload-from-device', UploadFromDevice);
Vue.component('regional-channels-list', RegionalChannelsList);
Vue.component('crossposts-manager', CrosspostsManager);
Vue.component('channel-select', ChannelSelect);
Vue.component('date-select', DateSelect);


Vue.component('permissions-manager', PermissionsManager);
//Vue.component('channels-manager', ChannelsManager);
Vue.component('channels-order-manager', ChannelsOrderManager);
Vue.component('smiles-manager', SmilesManager);
//Vue.component('users-manager', UsersManager);
Vue.component('categories-manager', CategoriesManager);
Vue.component('programs-manager', ProgramsManager);
Vue.component('tags-editor', TagsEditor);
Vue.component('article-bindings-editor', ArticleBindingsEditor);
