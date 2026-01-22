import { ref } from 'vue';
import { defineStore } from "pinia";

export const useChannelsStore = defineStore('channels', () => {

    const loading = ref<boolean>(false);
    const channels = ref<Models.Channel[]>([]);
    const radioStations = ref<Models.Channel[]>([]);

    const names: {
        [key: string]: Models.Channel
    } = {};

    let promise: Promise<void>;

    const load = (force: boolean = false) => {
        if (channels.value?.length && !force) {
            return Promise.resolve();
        }
        if (promise && !force) {
            return promise;
        }
        promise = new Promise<void>(resolve => {
            loading.value = true;
            $.get(route('channels.ajax')).then(({data})=> {
                channels.value = data.channels.filter(channel => !channel.is_radio);
                radioStations.value = data.channels.filter(channel => channel.is_radio);

                buildNamesMap();
                loading.value = false;
                return resolve();
            });
        })
        return promise;
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

    return {
        load,
        loading,
        channels,
        radioStations,
    }

});
