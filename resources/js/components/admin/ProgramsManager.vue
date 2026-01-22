<template>
    <div class="programs-manager">

        <modal ref="mergeModal" title="Объединение программ" :loading="mergeLoading">
            <div class="input-container" v-if="!mergeToInterprogram">
                <label class="input-container__label">Программа</label>
                <div class="input-container__inner">
                    <select2 theme="default" :options="mergeOptions" v-model="mergeSecondProgramId"/>
                </div>
            </div>
            <label class="input-container input-container--checkbox">
                <input type="checkbox" v-model="mergeToInterprogram">
                <div class="input-container--checkbox__element"></div>
                <div class="input-container__label">Переместить видео в раздел с межпрограммным оформлением</div>
            </label>
            <div class="form__bottom form__bottom--with-margin">
                <a @click="merge()" class="button button--light">Выбрать</a>
                <Response :light="true" :data="mergeResponse"/>
            </div>
        </modal>

        <div class="programs-manager__form">
            <div class="form">
                <div class="form__preloader" v-if="loading"></div>
                <div class="programs-manager__cols">
                    <div class="programs-manager__col">
                        <draggable
                            group="programs"
                            key="without_genre"
                            itemKey="id"
                            v-model="programsWithoutGenre"
                            class="programs-manager__items"
                            #item="{element}">
                            <programs-manager-item
                                :program="element"
                                :key="element.name"
                                @merge="showMergeModal(element)"
                            />
                        </draggable>
                    </div>
                    <div class="programs-manager__col">
                        <div v-for="genre in genres" :key="genre.id">
                            <h3 class="programs-manager__heading">{{ genre.name }}</h3>
                            <draggable
                                group="programs"
                                :key="'genre_'+genre.id"
                                itemKey="id"
                                v-model="programsByGenre[genre.id]"
                                class="programs-manager__items"
                                #item="{element}"
                            >
                                <programs-manager-item
                                    :program="element"
                                    :key="element.name"
                                    @merge="showMergeModal(element)"
                                />
                            </draggable>
                        </div>

                    </div>
                </div>
                <br>
                <div class="form__bottom">
                    <a @click="saveOrder()" class="button button--light">Сохранить</a>
                    <response :light="true" :data="response"/>
                </div>
            </div>
        </div>
    </div>
</template>
<style lang="scss">
.programs-manager {
    &__cols {
        display: flex;
        gap: var(--col-margin);
    }

    &__col {
        flex: 1;
        display: flex;
        flex-direction: column;
        max-height: 75vh;
        overflow: auto;
    }

    &__heading {
        margin: .5em 0;
        font-size: 1.25em;
    }

    &__item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: .5em;
        margin: 0 0 .25em;
        background: var(--bg-darker-2);
        border: 1px solid var(--border-color);
        font-weight: 400;

        &__actions {
            white-space: nowrap;
        }

        &__action {
            margin: 0 0 0 .5em;
            font-size: .875em;
            text-decoration: underline;
            cursor: pointer;
        }
    }

    &__tabs {
        margin: 0 0 .5em;
    }

    &__items {
        flex: 1;
    }
}
</style>
<script lang="ts" setup>
import {computed, ref, useTemplateRef} from "vue";

import draggable from 'vuedraggable'
import Response from '../Response.vue'
import Modal from '../Modal.vue'
import ProgramsManagerItem from "@/components/programs-manager/ProgramsManagerItem.vue";

const props = defineProps<{
    channel: Models.Channel,
    genres: Models.Genre[],
    programs: Models.Program[]
}>();

const programs = ref<Models.Program>(props.programs);
const programsWithoutGenre = ref<Models.Program[]>(props.programs.filter(program => !program.genre_id));
const programsByGenre = ref<{
    [key: string]: Models.Program[]
}>({});

props.genres.forEach(genre => {
    programsByGenre.value[genre.id] = props.programs.filter(program => program.genre_id === genre.id);
});

const mergeSelectedProgram = ref<Models.Program>();
const mergeLoading = ref<boolean>(false);
const mergeSecondProgramId = ref<number>();
const mergeToInterprogram = ref<boolean>(false);
const mergeResponse = ref<Forms.Response>();
const mergeModalRef = useTemplateRef('mergeModal');

const mergeOptions = computed(() => {
    const programs = mergeSelectedProgram.value ? props.programs.filter(
        program => program.id !== mergeSelectedProgram.value.id
    ) : props.programs;
    return programs.map(program => {
        return {id: program.id, text: program.name};
    })
})

const merge = () => {
    mergeLoading.value = true;
    if (!mergeSelectedProgram.value) return;
    $.post(route('programs.merge'), {
        original_id: mergeSelectedProgram.value.id,
        merged_id: mergeSecondProgramId.value,
        is_interprogram: mergeToInterprogram.value
    }).done(res => {
        mergeLoading.value = false;
        mergeResponse.value = res;
        if (res.status) {
            mergeModalRef.value?.hide();
            const programId = mergeSelectedProgram.value!.id;
            const genreId = mergeSelectedProgram.value!.genre_id;
            programs.value = programs.value.filter(program => program.id !== programId);
            if (genreId && genreId > 0) {
                programsByGenre.value[genreId] = programsByGenre.value[genreId].filter(
                    program => program.id !== programId
                );
            } else {
                programsWithoutGenre.value = programsWithoutGenre.value.filter(
                    program => program.id !== programId
                );
            }
        }
    }).fail((xhr) => {
        mergeLoading.value = false;
        const error = xhr.responseJSON;
        mergeResponse.value = {
            status: 0,
            text: error.message === "" ? "Неизвестная ошибка" : error.message
        };
    })
}

const showMergeModal = (program: Models.Program) => {
    mergeSelectedProgram.value = program;
    mergeModalRef.value.show();
}

const loading = ref<boolean>(false);
const response = ref<Forms.Response>();
const saveOrder = () => {
    loading.value = true;
    const order = {};
    Object.keys(programsByGenre.value).forEach(genreId => {
        order[genreId] = programsByGenre.value[genreId].map(program => program.id);
    })
    order[-1] = programsWithoutGenre.value.map(program => program.id);
    $.post(route('channels.programs.save-list', props.channel.id), {order}).done(res => {
        loading.value = false;
        response.value = res;
    }).fail((xhr) => {
        loading.value = false;
        const error = xhr.responseJSON;
        response.value = {status: 0, text: error.message === "" ? "Неизвестная ошибка" : error.message};
    })
}
</script>
