Vue.component('names-history-editor', require('./components/NamesHistoryEditor.vue').default);
Vue.component('picture-uploader', require('./components/PictureUploader.vue').default);
Vue.component('permissions-manager', require('./components/PermissionsManager.vue').default);
Vue.component('video-form', require('./components/VideoForm.vue').default);

Vue.component('select2', {
    props: ['options', 'value', 'theme'],
    template: '<select><slot></slot></select>',
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
    },
    watch: {
        value: function (value) {
            // update value
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
