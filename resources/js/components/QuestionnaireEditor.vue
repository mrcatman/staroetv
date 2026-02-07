<template>
    <div class="questionnaire__editor form__content">
        <input type="hidden" name="questionnaire_data" :value="questionnaireJson"/>

        <input-container vertical label="Название опроса">
            <input class="input" v-model="questionnaire.title"/>
        </input-container>
        <input-container checkbox label="Несколько вариантов ответа">
            <input type="checkbox" v-model="questionnaire.multiple_variants">
            <div class="input-container--checkbox__element"></div>
        </input-container>
        <div class="questionnaire__editor__inner">
            <div class="row questionnaire__editor__row" v-for="(variant, $index) in questionnaire.variants"
                 :key="$index">
                <div class="questionnaire__editor__row__input-container">
                    <div class="input-container input-container--vertical">
                        <div class="input-container__inner">
                            <input v-model="variant.title" class="input"/>
                        </div>
                    </div>
                </div>
                <div class="questionnaire__editor__row__button-container">
                    <a class="button button--light" @click="questionnaire.variants.splice($index, 1)">Удалить</a>
                </div>
            </div>
        </div>
        <div class="form__bottom">
            <a class="button button--light" @click="addItem()">Добавить еще пункт</a>
        </div>

    </div>
</template>
<style lang="scss">
.questionnaire__editor {
    width: 100%;
    margin-top: 2em;

    &__top {
        padding: 1em;
        color: #333;
    }

    &__inner {
        width: 100%;
        display: flex;
        flex-direction: column;
        gap: .5em;
    }

    &__row {
        gap: .5em;

        &__input-container {
            flex: 1;
        }
    }


}
</style>
<script lang="ts" setup>
import { computed, ref } from 'vue';

import InputContainer from "@/components/InputContainer.vue";

const props = defineProps<{
    data?: Questionnaire
}>();

interface Questionnaire {
    title: string,
    multiple_variants: boolean,
    variants: {
        title: string
    }[]
}
const questionnaire = ref<Questionnaire>(props.data || {title: '', multiple_variants: false, variants: []});

const questionnaireJson = computed(() => {
    return JSON.stringify(questionnaire.value)
})

const addItem = () => {
    questionnaire.value.variants.push({
        title: ""
    });
}
</script>

