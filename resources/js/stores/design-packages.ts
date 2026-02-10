import { ref } from 'vue';
import { defineStore } from 'pinia';

export const useDesignPackagesStore = defineStore('DesignPackages', () => {

    const designPackages = ref<{
        [key: number]: Models.DesignPackage[]
    }>({});

    const load = (channelId: number) => {
        if (designPackages[channelId]) {
            return;
        }

        // @ts-ignore
        $.get(route('design.channels.ajax', channelId)).done(res => {
            designPackages.value[channelId] = res.data.design_packages;
        })
    }

    const find = (id: number, channelId: number) => {
        return designPackages.value[channelId]?.find((designPackage: Models.DesignPackage) => designPackage.id === id);
    }

    return {
        designPackages,
        load,
        find
    }
})
