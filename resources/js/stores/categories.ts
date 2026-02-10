import {computed, ref} from 'vue';
import { defineStore } from "pinia";

export const useCategoriesStore = defineStore('categories', () => {

    const loading = ref<boolean>(false);
    const categories = ref<Models.Genre[]>([]);
    let promise: Promise<void>;

    const load = (force: boolean = false) => {
        if (categories.value?.length && !force) {
            return Promise.resolve();
        }

        if (promise) {
            return promise;
        }

        promise = new Promise<void>(resolve => {
            loading.value = true;
            $.get(route('records.categories')).then(({data}) => {
                categories.value = data.categories;

                loading.value = false;
                return resolve();
            });
        })
        return promise;
    }

    const advertisingTypes = computed(() => {
        const types = (categories.value || []).filter(category => category.type === 'advertising').map(category => {
            return {id: category.id, text: category.name}
        });
        types.unshift({
            id: -1,
            text: 'Обычная'
        });
        return types;
    });

    const otherTypes = computed(() => {
        const types = (categories.value || []).filter(category => category.type === 'videos_other').map(category => {
            return {id: category.id, text: category.name}
        });
        types.unshift({
            id: -1,
            text: 'Другое'
        });
        return types;
    });

    const interprogramTypes = computed(() => {
        const types = (categories.value || []).filter(category => category.type === 'interprogram').map(category => {
            return {id: category.id, text: category.name}
        });
        types.unshift({
            id: -1,
            text: 'Другое'
        });
        return types;
    });

    const findById = (id: number) => {
        return categories.value.find(category => category.id === id);
    }

    return {
        load,
        categories,
        findById,

        otherTypes,
        interprogramTypes,
        advertisingTypes,
    }

});
