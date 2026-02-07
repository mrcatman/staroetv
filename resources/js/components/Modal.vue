<template>
    <div class="modal-window__content__inner" :id="randomId" v-if="visible">
        <slot :hide="hide"></slot>
    </div>
</template>
<style lang="scss">

</style>
<script lang="ts">
import {ModalParams} from "@/modules/modals";

let modal;
export default {
    props: ['title', 'loading', 'nopadding'],
    data() {
        return {
            visible: false,
            randomId: `vue_modal_${Math.floor(Math.random() * 1000000000)}`,
        }
    },
    methods: {
        hide() {
            window.closeModal(modal);
        },
        async show(params: ModalParams) {
            this.visible = true;
            this.$nextTick(() => {
                modal = window.showModal(`#${this.randomId}`, this.title, () => this.visible = false, params);
            })
        },
        centerY() {
            window.centerY(modal);
        }
    }
}
</script>
