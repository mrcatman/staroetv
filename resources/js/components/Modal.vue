<template>
    <div class="modal-window__content__inner" :id="randomId" v-if="visible">
        <slot></slot>
    </div>
</template>
<style lang="scss">

</style>
<script>

   let modal;
    export default {
        props: ['title', 'loading', 'nopadding'],
        data () {
            return {
                visible: false,
                randomId: `vue_modal_${Math.floor(Math.random() * 1000000000)}`,
            }
        },
        methods: {
            hide() {
                this.visible = false;
              //  window.closeModal(modal);
            },
            async show() {
                this.visible = true;
                this.$nextTick(() => {
                    modal = window.showModal(`#${this.randomId}`, this.title, this.hide);
                })

            },
        }
    }
</script>
