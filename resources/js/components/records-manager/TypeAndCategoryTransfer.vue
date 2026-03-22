<template>
    <input type="hidden" name="type" v-model="type"/>
    <input type="hidden" v-if="type === 'interprogram'" name="interprogram_type" v-model="interprogramType"/>
    <input type="hidden" v-if="type === 'other'" name="other_category_id" v-model="otherCategoryId"/>

    <input-container
        autowidth
        vertical
        label="Тип"
    >
        <type-select v-model="type"/>
    </input-container>

        <input-container vertical label="Тип" v-if="type === 'interprogram'">
            <select2 theme="default" :options="categoriesStore.interprogramTypes" v-model="interprogramType"/>
        </input-container>


        <input-container vertical label="Тип" v-if="type === 'other'">
            <select2 theme="default" :options="categoriesStore.otherTypes" v-model="otherCategoryId"/>
        </input-container>

</template>
<script setup lang="ts">
import { ref } from "vue";
import { useCategoriesStore } from "@/stores/categories";

import InputContainer from "@/components/InputContainer.vue";

const type = ref<Records.Type>('interprogram');
const interprogramType = ref<number>(22);
const otherCategoryId = ref<number>();

const categoriesStore = useCategoriesStore();
categoriesStore.load();

</script>
