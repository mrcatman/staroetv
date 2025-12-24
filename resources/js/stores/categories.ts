import { ref } from 'vue';
import { defineStore } from "pinia";

export const useCategoriesStore = defineStore('categories', () => {

    const categories = ref<Models.Genre[]>([]);

    const load = () => {
        return new Promise<void>(resolve => {
            if (categories.value?.length) {
                return resolve();
            }
            $.get(route('records.categories')).then(({data}) => {
                categories.value = data.categories;
            });
        })
    }


    return {
        load,
        categories,
    }

});
