import { ref } from 'vue';
import { defineStore } from 'pinia';

export const useProgramsStore = defineStore('programs', () => {

    const loading = ref<boolean>(false);
    const programs = ref<{
        [key: number]: Models.Program[]
    }>({});

    const promises: {
        [key: number]: Promise<void>
    } = {};

    const load = (channelId: number, force: boolean = false) => {
        if (programs.value[channelId] && !force) {
            return Promise.resolve();
        }
        if (promises[channelId] && !force) {
            return promises[channelId];
        }
        promises[channelId] = new Promise<void>(resolve => {
            loading.value = true;

            $.get(route('channels.programs.ajax', channelId)).done(res => {
                programs.value[channelId] = res.data.programs;
                loading.value = false;
                return resolve();
            })
        })
        return promises[channelId];
    }

    const findByNameAndChannelId = (name: string, channelId: number) => {
        const _name = name.toLocaleLowerCase();
        const program = programs.value[channelId]?.find((program: Models.Program) => program.name.toLocaleLowerCase() === _name);
        if (program) {
            return program;
        }
        return programs.value[channelId]?.find((program: Models.Program) => program.name.toLocaleLowerCase().includes(_name));
    }

    return {
        load,
        loading,
        programs,
        findByNameAndChannelId
    }
})
