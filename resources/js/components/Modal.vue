<template>
    <div :id="randomId" v-if="visible">
        <slot :hide="hide"></slot>
    </div>
</template>
<style lang="scss">

</style>
<script lang="ts" setup>
import { nextTick, ref } from "vue";
import { ModalParams } from "@/modules/modals";

let modal;

const props = defineProps<{
    title: string,
    loading?: boolean,
}>();

const visible = ref(false);
const randomId = `vue_modal_${Math.floor(Math.random() * 1000000000)}`;

const show = async (params?: ModalParams) => {
    visible.value = true;
    await nextTick();
    modal = window.showModal(`#${randomId}`, props.title, () => visible.value = false, params);
}
const hide = () => {
    window.closeModal(modal);
}
// const centerY = () => {
//     window.centerY(modal);
// }

defineExpose({
    show,
    hide,
    //centerY
});
</script>
