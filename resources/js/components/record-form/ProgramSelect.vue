<template>
    <div class="input-container__element-outer">
        <Preloader v-if="loading && !disabled" />
        <div class="autocomplete">
            <div class="row">
                <div class="col">
                    <div class="input-container__inner input-container__inner--with-button">
                        <div class="input-container__element-outer">
                            <input class="input" @change="onNameChange()" v-model="program.name"
                                   :disabled="disabled || program.unknown" placeholder="Поиск программ канала по названию..."/>
                            <a v-if="program.name.length && !program.id && !program.unknown" class="input-container__button input-container__button--big input-container__button--info">
                                <span class="tooltip">Будет создана новая программа</span>
                                <i class="fa fa-exclamation-circle"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col col--auto">
                    <label class="input-container input-container--checkbox">
                        <input type="checkbox" v-model="program.unknown">
                        <div class="input-container--checkbox__element"></div>
                        Программа неизвестна
                    </label>
                </div>

            </div>

            <div class="autocomplete__items autocomplete__items--vertical" v-show="!program.unknown">
                <a
                    v-for="filteredProgram in filteredPrograms"
                    :key="filteredProgram.id"
                    @click="selectProgram(filteredProgram)"
                    class="autocomplete__item autocomplete__item--vertical"
                    :class="{'autocomplete__item--selected': program && program.id === filteredProgram.id }"
                >
                <span v-if="filteredProgram.cover" class="autocomplete__item__logo"
                      :style="{backgroundImage: `url(${filteredProgram.cover})`}"></span>

                    <div class="autocomplete__item__texts">
                        <span class="autocomplete__item__name">{{ filteredProgram.name }}</span>
                    </div>

                </a>
            </div>
        </div>

    </div>
</template>
<script lang="ts" setup>
import { computed, defineModel } from 'vue';
import { storeToRefs } from "pinia";
import { useProgramsStore } from "@/stores/programs.js";
import { RecordsUploadRelationData } from "@/composables/record-form";
import Preloader from "@/components/Preloader.vue";

const { loading, programs } = storeToRefs(useProgramsStore());

const emit = defineEmits<{ (e: 'selected'): void }>();

const props = defineProps<{
    disabled?: boolean,
    channel: Models.Channel,
}>();

const program = defineModel<RecordsUploadRelationData>('program', {
    default: {
        id: null,
        name: ''
    }
});

const filteredPrograms = computed(() => {
    const _programs = programs.value[props.channel.id] ?? [];
    if (program.value.name === '') {
        return _programs; //.slice(0, 20);
    } else {
        const lowercaseName = program.value.name.toLowerCase();
        return _programs.filter(program => {
            return program.name.toLowerCase().includes(lowercaseName);
        }).sort((a, b) => {
            return b.index - a.index;
        })
    }
});


const selectProgram = (_program: Models.Program) => {
    program.value.id = _program.id;
    program.value.name = _program.name;
    emit('selected');
}

let findByNameTimeout;
const onNameChange = () => {
    program.value.id = null;

    clearTimeout(findByNameTimeout);
    findByNameTimeout = setTimeout(findByName, 500);
}

const findByName = () => {
    const lowercaseName = program.value.name.trim().toLowerCase();
    if (!lowercaseName.length) {
        return;
    }
    const foundProgram = filteredPrograms.value.filter(program => {
        return program.name.toLowerCase() === lowercaseName;
    })[0];
    if (foundProgram) {
        selectProgram(foundProgram);
    }
}
</script>
