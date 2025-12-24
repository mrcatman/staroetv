import { ref } from 'vue';
import { defineStore } from 'pinia';

export const useDesignPackagesStore = defineStore('DesignPackages', () => {

    const designPackages = ref<{
        [key: number]: Models.DesignPackage[]
    }>();

    const load = (channelId: number) => {
        if (designPackages[channelId]) {
            return;
        }

        // @ts-ignore
        $.get(route('channels.design.ajax', channelId)).done(res => {
            designPackages[channelId] = res.data.design_packages;
        })
    }

    const findByNameAndChannelId = (name: string, channelId: number) => {
        return designPackages[channelId]?.find((DesignPackage: Models.DesignPackage) => DesignPackage.name.toLocaleLowerCase().includes(name.toLocaleLowerCase()));
    }

    return {
        load,
        findByNameAndChannelId
    }
})
