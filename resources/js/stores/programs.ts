import { ref } from 'vue';
import { defineStore } from 'pinia';

export const useProgramsStore = defineStore('programs', () => {

    const loading = ref<boolean>(true);
    const programs = ref<{
        [key: number]: Models.Program[]
    }>({});

    const load = (channelId: number) => {
        return new Promise<void>(resolve => {
            if (programs.value[channelId]) {
                resolve();
            }

            loading.value = true;

            // @ts-ignore
            $.get(route('channels.programs.ajax', channelId)).done(res => {
                programs.value[channelId] = res.data.programs;
                loading.value = false;
                resolve();
            })
        })

    }

    const findByNameAndChannelId = (name: string, channelId: number) => {
        return programs.value[channelId]?.find((program: Models.Program) => program.name.toLocaleLowerCase().includes(name.toLocaleLowerCase()));
    }

    return {
        load,
        loading,
        programs,
        findByNameAndChannelId
    }
})
