<template>
    <select :name="name">
        <slot></slot>
    </select>
</template>
<script>
   export default {
        props: ['options', 'value', 'theme', 'name', 'customOptions'],
        data() {
            return {
                ready: false
            }
        },
        mounted() {
            let customOptions = this.customOptions || {};
            let vm = this;
            $(this.$el).select2({ data: this.options, theme : this.theme, ...customOptions }).val(this.value).trigger('change').on('change', function() {
                console.log('selected', $(this).val());
                vm.$emit('input', $(this).val())
            });
            setTimeout(() => {
                this.ready = true;
            }, 500)
        },
        watch: {
            value(value) {
                if (this.ready) {
                    this.$emit('change', this.value)
                }
                //$(this.$el).val(value).trigger('change')
            },
            options(options) {
                $(this.$el).empty().select2({ data: options });
                setTimeout(() => {
                    $(this.$el).val(this.value).trigger('change')
                }, 1);
            }
        },
        destroyed() {
            $(this.$el).off().select2('destroy')
        }
    }
</script>