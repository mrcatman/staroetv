<template>
    <div class="player-embed" v-if="record.use_own_player">
        <video
            class="own-player"
            :data-title="record.title"
            :data-url="record.url"
            :data-id="record.id"
            :data-poster="record.cover"
            controls
        >
            <source
                :src="record.source_hls ?? record.source_telegram"
                :type="record.source_hls ? 'application/vnd.apple.mpegurl' : 'video/mp4'"
            >
        </video>
    </div>
    <div v-else class="player-embed" v-html="record.embed_code" />
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
<style lang="scss" scoped>
.player-embed {
    aspect-ratio: 4 / 3;
    :global(.plyr, iframe) {
        height: 100%;
    }
}
</style>
