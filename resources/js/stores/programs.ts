import { ref } from 'vue';
import { defineStore } from 'pinia';

export const useProgramsStore = defineStore('programs', () => {

    const programs = ref<{
        [key: number]: Models.Program[]
    }>();

    const load = (channelId: number) => {
        if (programs[channelId]) {
            return;
        }

        // @ts-ignore
        $.get(route('channels.programs.ajax', channelId)).done(res => {
            programs[channelId] = res.data.programs;
        })
    }

    const findByNameAndChannelId = (name: string, channelId: number) => {
        return programs[channelId]?.find((program: Models.Program) => program.name.toLocaleLowerCase().includes(name.toLocaleLowerCase()));
    }

    return {
        load,
        findByNameAndChannelId
    }
})
