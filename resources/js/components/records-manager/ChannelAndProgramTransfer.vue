<template>
    <input type="hidden" name="channel_id" v-model="channelId"/>
    <input type="hidden" name="program_id" v-model="programId"/>

    <input-container
        vertical
        label="Канал"
    >
        <select2 theme="default" :options="channelOptions" v-model="channelId"/>
    </input-container>
    <input-container
        vertical
        label="Передача"
    >
        <select2 theme="default" :options="programOptions" v-model="programId"/>
    </input-container>
</template>
<script setup lang="ts">
import { watch, computed, ref } from "vue";

import { useChannelsStore } from "@/stores/channels";
import { useProgramsStore } from "@/stores/programs";

import InputContainer from "@/components/InputContainer.vue";

const channelsStore = useChannelsStore();
const programsStore = useProgramsStore();

const props = defineProps<{
    channelId?: number,
    programId?: number,
}>();

channelsStore.load();

const channelId = ref<number>(props.channelId);
const programId = ref<number>(props.programId);

const channelOptions = computed(() => {
    return ([...channelsStore.channels, ...channelsStore.radioStations]).map(channel => {
        return {
            id: channel.id,
            text: channel.name,
        }
    });
});

const programOptions = computed(() => {
    return (programsStore.programs[channelId.value] || []).map(program => {
        return {
            id: program.id,
            text: program.name,
        }
    });
});

const onChannelChange = () => {
    channelId.value > 0 && programsStore.load(channelId.value);
}
onChannelChange();
watch(() => channelId.value, onChannelChange);
</script>
