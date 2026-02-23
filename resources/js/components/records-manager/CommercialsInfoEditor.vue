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
        <input class="input" :placeholder="autoUpdateTitle ? 'Заголовок сгенерируется автоматически' : 'Заголовок'"
               readonly v-model="data.title"/>
        <a @click="updateTitle()" v-if="!autoUpdateTitle"
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

    <a class="button" v-if="hideAdditional" @click="hideAdditional = false">Показать доп. поля</a>
    <template v-else>
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

</template>
<script setup lang="ts">
import { ref, watch } from "vue";
import { autocompleteOptions } from "@/utils/autocomplete";
import { useCategoriesStore } from "@/stores/categories";

import InputContainer from "@/components/InputContainer.vue";
import { generateTitle } from "@/utils/records/generate-title";

const categoriesStore = useCategoriesStore();
categoriesStore.load();

const props = defineProps<{
    record: Models.Record,
    autoUpdateTitle?: boolean,
    hideAdditional?: boolean,
}>();
const hideAdditional = ref(props.hideAdditional);

const model = defineModel<Models.Record>();
const data = ref<Models.Record>(props.record ?? model.value);
if (!data.value.advertising_type) {
    data.value.advertising_type = -1;
}

const emit = defineEmits<{
    (e: 'change'): void
}>();

watch(() => data, () => {
    model.value = data.value;
}, {deep: true})

watch(() => model, () => {
    data.value = model.value;
}, {deep: true})

const updateTitle = () => {
    const date = {
        year: data.value.year,
        year_start: data.value.year_start,
        year_end: data.value.year_end,
    }
    data.value.title = generateTitle({
        type: 'advertising',
        advertising: {
            brand: data.value.advertising_brand,
        },
        short_description: data.value.short_description,
        date,
    })
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

let changeTimeout;

watch(() => [
    data.value.advertising_category,
    data.value.advertising_brand,
    data.value.year,
    data.value.year_start,
    data.value.year_end,
], () => {
    changeTimeout = setTimeout(() => {
        emit('change');
    }, 500);

    if (props.autoUpdateTitle) {
        updateTitle();
    }
})

watch(() => [
    data.value.short_description,
    data.value.region,
    data.value.country,
], () => {
    if (props.autoUpdateTitle) {
        updateTitle();
    }
});

if (props.autoUpdateTitle) {
    updateTitle();
}
</script>
