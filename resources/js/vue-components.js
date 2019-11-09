import { BTable } from 'bootstrap-vue'
import { BPagination } from 'bootstrap-vue'
Vue.component('b-table', BTable)
Vue.component('b-pagination', BPagination)

Vue.component('names-history-editor', require('./components/NamesHistoryEditor.vue').default);
Vue.component('picture-uploader', require('./components/PictureUploader.vue').default);
Vue.component('record-form', require('./components/RecordForm.vue').default);
Vue.component('user-groups-select', require('./components/UserGroupsSelect.vue').default);
Vue.component('datepicker', require('vuejs-datepicker').default);
Vue.component('pagination', require('laravel-vue-pagination'));
Vue.component('records-search', require('./components/RecordsSearch').default);
Vue.component('crossposts-editor', require('./components/CrosspostsEditor').default);

Vue.component('permissions-manager', require('./components/admin/PermissionsManager.vue').default);
Vue.component('channels-manager', require('./components/admin/ChannelsManager.vue').default);
Vue.component('channels-order-manager', require('./components/admin/ChannelsOrderManager.vue').default);
Vue.component('smiles-manager', require('./components/admin/SmilesManager.vue').default);
Vue.component('users-manager', require('./components/admin/UsersManager.vue').default);

Vue.component('select2', {
    props: ['options', 'value', 'theme', 'name'],
    template: '<select :name="name"><slot></slot></select>',
    data() {
        return {
            ready: false
        }
    },
    mounted: function () {
        var vm = this;
        $(this.$el)
        // init select2
            .select2({ data: this.options, theme : this.theme })
            .val(this.value)
            .trigger('change')
            // emit event on change.
            .on('change', function () {
                vm.$emit('input', this.value)
            })
        setTimeout(() => {
            this.ready = true;
        }, 500)
    },
    watch: {
        value: function (value) {
            // update value
            if (this.ready) {
                this.$emit('change', this.value)
            }
            $(this.$el)
                .val(value)
                .trigger('change')
        },
        options: function (options) {
            // update options
            $(this.$el).empty().select2({ data: options });
            setTimeout(() => {
                $(this.$el).val(this.value).trigger('change')
            }, 1);
        }
    },
    destroyed: function () {
        $(this.$el).off().select2('destroy')
    }
});
