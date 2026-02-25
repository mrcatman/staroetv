<template>
    <video
        v-if="record.use_own_player"
        class="own-player"
        :data-title="record.title"
        :data-url="record.url"
        :data-id="record.id"
        :data-poster="record.cover"
        controls
    >
        <source
            :src="record.source_path ? record.source_hls : record.source_telegram"
            :type="record.source_path ? 'application/vnd.apple.mpegurl' : 'video/mp4'"
        >
    </video>
    <div v-else v-html="record.embed_code" />
</template>
<script lang="ts" setup>
import { initPlayer } from '../modules/player'
import { nextTick, onMounted, watch } from "vue";

const props = defineProps<{
    record: Models.Record
}>();

const onPlayerChange = async() => {
    await nextTick();

    if (props.record?.use_own_player) {
        initPlayer();
    }
}

onMounted(onPlayerChange);
watch(() => props.record, onPlayerChange);
</script>
