import { ref } from 'vue';
import { defineStore } from "pinia";

export const useChannelsStore = defineStore('channels', () => {

    const loading = ref<boolean>(true);
    const channels = ref<Models.Channel[]>([]);
    const names: {
        [key: string]: Models.Channel
    } = {};

    const load = () => {
        if (channels.value?.length) {
            return;
        }
        loading.value = true;
        $.get(route('channels.ajax')).then(({data})=> {
            channels.value = data.channels;
            buildNamesMap();
            loading.value = false;
        });
    }

    const buildNamesMap = () => {
        channels.value.forEach((channel: Models.Channel) => {
            names[channel.name] = channel;
            if (channel.names?.length) {
                channel.names.forEach(channelName => {
                    names[channelName.name] = channel;
                })
            }
        });
    }

    const findByName = (name: string): Models.Channel => {
        return names[name];
    }

    return {
        load,
        loading,
        channels,
        findByName,
    }

});
