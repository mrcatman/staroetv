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
       methods: {
           setData(data) {
               $(this.$el).data().select2.updateSelection(data);
           }
       },
       mounted() {
           let customOptions = this.customOptions || {};
           let vm = this;
           $(this.$el).select2({
               data: this.options,
               theme: this.theme, ...customOptions
           }).val(this.value).trigger('change').on('change', function () {
               vm.$emit('input', $(this).val())
           });
           setTimeout(() => {
               this.ready = true;
           }, 500)
       },
       watch: {
           value(value) {
               if (value == this.value) {
                   return;
               }
               if (this.ready) {
                   this.$emit('change', this.value)
               }
               $(this.$el).val(value).trigger('change')
           },
           options(options) {
               $(this.$el).empty().select2({data: options});
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
