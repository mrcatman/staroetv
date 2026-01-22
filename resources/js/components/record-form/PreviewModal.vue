<template>
    <modal class="preview-modal" ref="preview" title="Предпросмотр">
        <div class="preview-modal__container" v-if="record.code" v-html="record.code"></div>
        <div v-else>
            <video class="preview-modal__video" v-if="!isRadio" controls ref="video" />
            <audio class="preview-modal__audio" v-else controls ref="audio" />
        </div>
    </modal>
</template>
<style lang="scss">
.preview-modal {
    &__container {
        position: relative;
        aspect-ratio: 4 / 3;
        iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }
    }

    &__video {
        background: #111;
        aspect-ratio: 4 / 3;
        width: 100%;
    }

    &__audio {
        width: 100%;
    }
}
</style>
<script setup lang="ts">
import { nextTick, useTemplateRef } from "vue";

import Modal from "@/components/Modal.vue";
import { MultipleRecordsResponseItem } from "../MassUploader.vue";

const props = defineProps<{
    record: MultipleRecordsResponseItem,
    isRadio: boolean,
}>();

const previewRef = useTemplateRef<typeof Modal>('preview');
const videoRef = useTemplateRef<HTMLVideoElement>('video');
const audioRef = useTemplateRef<HTMLAudioElement>('audio');
const show = async () => {
    previewRef.value?.show({backdrop: true});
    if (props.record.upload) {
        await nextTick();

        const url = URL.createObjectURL(props.record.upload);
        const el = props.isRadio ? audioRef.value : videoRef.value;
        el.src = url;
        el.play();
    }
}

defineExpose({show});
</script>
