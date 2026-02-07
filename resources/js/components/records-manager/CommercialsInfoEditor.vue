<template>
    <input type="hidden" name="id" :value="data.id"/>
    <input type="hidden" name="title" :value="data.title"/>
    <input type="hidden" name="short_description" :value="data.short_description"/>
    <input type="hidden" name="description" :value="data.description"/>

    <input type="hidden" name="advertising_brand" :value="data.advertising_brand"/>
    <input type="hidden" name="advertising_category" :value="data.advertising_category"/>
    <input type="hidden" name="advertising_type" :value="data.advertising_type"/>
    <input type="hidden" name="region" :value="data.region"/>
    <input type="hidden" name="country" :value="data.country"/>

    <input-container
        vertical
        with-button
    >
        <input class="input" readonly v-model="data.title"/>
        <a @click="updateTitle()"
           class="input-container__button">
            <span class="tooltip">Обновить заголовок</span>
            <i class="fa fa-sync"></i>
        </a>
    </input-container>

    <div class="row">
        <div class="col">

            <input-container vertical label="Что рекламируется">
                <select2
                    theme="default"
                    :customOptions="brandsAutocompleteOptions"
                    v-model="data.advertising_brand"
                />
            </input-container>
        </div>
        <div class="col">
            <input-container vertical label="Категория">
                <select2
                    theme="default"
                    :customOptions="categoriesAutocompleteOptions"
                    v-model="data.advertising_category"
                />
            </input-container>
        </div>
    </div>
    <input-container
        vertical
        label="Слоган / вариация сюжета и т.д."
    >
        <input class="input" v-model="data.short_description"/>
        <template #description>
            Если слоган слишком длинный, лучше вынести его в поле описания ниже
        </template>
    </input-container>

    <div class="horisontal-delimiter"></div>
    <input-container
        vertical
        label="Описание"
    >
        <textarea class="input input--textarea" v-model="data.description"/>
    </input-container>
    <input-container vertical label="Тип рекламы">
        <select2
            theme="default"
            :options="categoriesStore.advertisingTypes"
            v-model="data.advertising_type"
        />
    </input-container>
    <div class="row">
        <div class="col">
            <input-container vertical label="Город/регион">
                <select2
                    theme="default"
                    :customOptions="regionsAutocompleteOptions"
                    v-model="data.region"
                />
            </input-container>
        </div>
        <div class="col">
            <input-container vertical label="Страна">
                <select2
                    theme="default"
                    :customOptions="countriesAutocompleteOptions"
                    v-model="data.country"
                />
            </input-container>
        </div>
    </div>


</template>
<script setup lang="ts">
import { ref, watch } from "vue";
import { autocompleteOptions } from "@/utils/autocomplete";
import { useCategoriesStore } from "@/stores/categories";

import InputContainer from "@/components/InputContainer.vue";

const categoriesStore = useCategoriesStore();
categoriesStore.load();

const props = defineProps<{
    record: Models.Record,
}>();

const model = defineModel<Models.Record>();
const data = ref<Models.Record>(props.record ?? model.value);
if (!data.value.advertising_type) {
    data.value.advertising_type = -1;
}

watch(() => data, () => {
    model.value = data.value;
}, {deep: true})

watch(() => model, () => {
    data.value = model.value;
}, {deep: true})

const updateTitle = () => {
    data.value.title = `${data.value.advertising_brand} (${data.value.year}) ${data.value.short_description}${data.value.region ? ` (${data.value.region})` : (data.value.country ? ` (${data.value.country})` : '')}`;
}

const regionsAutocompleteOptions = autocompleteOptions(data.value.region, () => {
    return route('records.autocomplete.regions', {country: data.value.advertising_country});
});
const countriesAutocompleteOptions = autocompleteOptions(data.value.country, route('records.autocomplete.countries'));
const brandsAutocompleteOptions = autocompleteOptions(data.value.advertising_brand, () => {
    return route('records.autocomplete.commercials-brands', {advertising_type: data.value.advertising_type});
});
const categoriesAutocompleteOptions = autocompleteOptions(data.value.advertising_type, () => {
    return route('records.autocomplete.commercials-categories', {advertising_type: data.value.advertising_type});
});

</script>
