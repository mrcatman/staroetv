<template>
    <similar-modal ref="similarModal" :similar="similar" @mark="markSimilarAsChecked"/>
    <preview-modal ref="previewModal" :record="record" :is-radio="isRadio"/>

    <div class="mass-uploader__record">
        <Preloader v-if="loading"/>
        <div v-else-if="addedRecord" class="mass-uploader__record__added">
            <span class="mass-uploader__record__added__check">
                <i class="fa fa-check"></i>
            </span>
            <a target="_blank" :href="addedRecord.url" class="mass-uploader__record__added__title">
                {{ addedRecord.title }}
                <i class="fa fa-external-link-alt"></i>
            </a>
            <a :href="_route(`records.${addedRecord.is_radio ? 'radio' : 'video'}.edit`, addedRecord.id)"
               target="_blank" class="mass-uploader__record__added__edit">
                <i class="fa fa-edit"></i>
            </a>
            <div class="mass-uploader__record__added__picture" v-if="addedRecord.cover"
                 :style="{backgroundImage: `url(${addedRecord.cover})`}"></div>
        </div>

        <div class="form__content mass-uploader__record__content" v-else>
            <response :data="response"/>
            <div class="mass-uploader__record__top">
                <div class="form__content mass-uploader__record__top__left">
                    <div class="row mass-uploader__record__top__inner">
                        <div class="col col--auto">
                            <input-container autowidth vertical label="Тип" :errors="errors.type">
                                <type-select v-model="data.type"/>
                            </input-container>
                        </div>

                        <div class="buttons-row mass-uploader__record__buttons">
                            <a @click="previewRef.show()" class="button">
                                <i class="fa fa-play"></i>
                                Предпросмотр
                            </a>
                            <a class="button button--light" @click="remove()">
                                <i class="fa fa-trash"></i>
                                Удалить
                            </a>
                        </div>
                    </div>
                    <div class="row row--align-end">
                        <div class="col">
                            <input-container vertical with-button label="Заголовок" :errors="errors.title">
                                <input ref="title" class="input" :readonly="titleLocked" v-model="data.title"/>
                                <a @click="titleLocked = !titleLocked"
                                   :title="titleLocked ? 'Ввести название вручную' : 'Сбросить название'"
                                   class="input-container__button">
                                    <i v-if="titleLocked" class="fa fa-edit"></i>
                                    <i v-else class="fa fa-times"></i>
                                </a>
                            </input-container>
                        </div>
                        <div v-if="!data.record.upload" class="col col--auto">
                            <input-container checkbox label="Загрузить в хранилище сайта">
                                <input type="checkbox" v-model="data.record.move_to_storage">
                                <div class="input-container--checkbox__element"></div>
                            </input-container>
                        </div>
                    </div>

                </div>
                <div class="mass-uploader__record__picture" v-if="data.record.thumbnail_url"
                     :style="{backgroundImage: `url(${data.record.thumbnail_url})`}"></div>
            </div>
            <input-container
                vertical
                with-button
                label="Дата"
                :errors="errors.date"
            >
                <date-select v-model="data.date"
                             :range="['advertising', 'interprogram', 'program-design'].includes(data.type) && data.date.range"/>
                <template #toggleButtons
                          v-if="['advertising', 'interprogram', 'program-design'].includes(data.type)">
                    <a class="input-container__toggle-button" v-if="!data.date.range"
                       @click="data.date.range = true">Указать временной промежуток показа
                    </a>
                    <a class="input-container__toggle-button" v-else
                       @click="data.date.range = false">
                        Указать конкретную дату
                    </a>
                </template>
            </input-container>
            <div class="row row--align-start">
                <div class="col"
                     v-if="['programs', 'interprogram', 'program-design'].includes(data.type)">
                    <input-container
                        vertical
                        :with-button="!!(data.channel.name.length && data.channel.id <= -1 && !data.channel.unknown)"
                        :label="isRadio ? 'Радиостанция' : 'Канал'"
                        :errors="errors.channel"
                    >
                        <select2 theme="default" :options="channelOptions" v-model="data.channel.id"
                                 v-model:name="data.channel.name" :customOptions="createTagOptions"/>

                        <a v-if="data.channel.name.length && data.channel.id <= -1 && !data.channel.unknown"
                           class="input-container__button input-container__button--select input-container__button--big input-container__button--info">
                            <span class="tooltip">Будет создан новый канал</span>
                            <i class="fa fa-exclamation-circle"></i>
                        </a>
                        <template #toggleButtons>
                            <a class="input-container__toggle-button"
                               @click="channelsStore.load(true)">Перезагрузить</a>
                        </template>
                    </input-container>

                </div>

                <div class="col"
                     v-if="['programs', 'program-design'].includes(data.type) && data.channel.id">

                    <input-container
                        vertical
                        :with-button="!!(data.program.name.length && data.program.id <= -1 && !data.program.unknown)"
                        label="Программа"
                        :errors="errors.program"
                    >
                        <select2 theme="default" :options="programOptions" v-model="data.program.id"
                                 v-model:name="data.program.name" :customOptions="createTagOptions"/>
                        <a v-if="data.program.name.length && data.program.id <= -1 && !data.program.unknown"
                           class="input-container__button input-container__button--select input-container__button--big input-container__button--info">
                            <span class="tooltip">Будет создана новая программа</span>
                            <i class="fa fa-exclamation-circle"></i>
                        </a>
                        <template #toggleButtons>
                            <a class="input-container__toggle-button"
                               @click="programsStore.load(data.channel.id, true)">Перезагрузить</a>
                        </template>
                    </input-container>
                </div>


                <div class="col" v-if="data.type === 'interprogram'">
                    <input-container vertical label="Тип" :errors="errors.interprogram_type">
                        <select2 theme="default" :options="categoriesStore.interprogramTypes"
                                 v-model="data.interprogram.type"/>
                    </input-container>
                </div>

                <div class="col" v-if="data.type === 'other'">
                    <input-container vertical label="Тип" :errors="errors.other_category_id">
                        <select2 theme="default" :options="categoriesStore.otherTypes"
                                 v-model="data.other.category_id"/>
                    </input-container>
                </div>

                <template v-if="data.type === 'advertising'">
                    <div class="col">
                        <input-container vertical label="Что рекламируется" :errors="errors.advertising_brand">
                            <select2
                                theme="default"
                                :customOptions="brandsAutocompleteOptions"
                                v-model="data.advertising.brand"
                            />
                        </input-container>
                    </div>
                    <div class="col">
                        <input-container vertical label="Категория" :errors="errors.advertising_category">
                            <select2
                                theme="default"
                                :customOptions="categoriesAutocompleteOptions"
                                v-model="data.advertising.category"
                            />
                        </input-container>
                    </div>
                    <div class="col">
                        <input-container vertical label="Тип рекламы" :errors="errors.advertising_type">
                            <select2
                                theme="default"
                                :options="categoriesStore.advertisingTypes"
                                v-model="data.advertising.type"
                            />
                        </input-container>
                    </div>
                    <div class="col">
                        <input-container vertical label="Город/регион" :errors="errors.region">
                            <select2
                                theme="default"
                                :customOptions="regionsAutocompleteOptions"
                                v-model="data.advertising.region"
                            />
                        </input-container>

                    </div>
                    <div class="col">
                        <input-container vertical label="Страна" :errors="errors.country">
                            <select2
                                theme="default"
                                :customOptions="countriesAutocompleteOptions"
                                v-model="data.advertising.country"
                            />
                        </input-container>
                    </div>
                </template>

                <!--
               <div class="inputs-line__item"
                    v-if="record.is_interprogram && !record.is_program_design && record.channel.id && interprogramOptions[record.channel.id]">
                   <div class="inputs-line__item__label">Пакет оформления</div>
                   <select2 theme="default" :options="interprogramOptions[record.channel.id]"
                            v-model="record.interprogram_package_id"></select2>
                   <a class="input-container__toggle-button"
                      @click="loadInterprogramPackages(record, true)">Перезагрузить</a>
               </div>
               -->
            </div>

            <div class="row" v-if="files.length">
                <div class="col">
                    <input-container vertical label="Файл на сервере" :errors="errors.uploaded_file_url">
                        <select2 theme="default" :options="files" v-model="data.record.uploaded_file_url"/>
                    </input-container>
                </div>
            </div>

            <input-container vertical label="Короткое описание" :errors="errors.short_description">
                <textarea class="input input--textarea" v-model="data.short_description"></textarea>
            </input-container>

            <input-container vertical label="Описание" :errors="errors.description">
                <textarea class="input input--textarea" v-model="data.description"></textarea>
            </input-container>

            <input-container vertical label="Источник" :errors="errors.source">
                <input class="input"  v-model="data.source" />
            </input-container>

            <div class="form__bottom">
                <button :disabled="saving" @click="saveRecord()" class="button">Добавить</button>
                <div class="form__progress" v-if="isUploadingFile">
                    <div class="form__progress__bar" :style="{width: uploadPercent + '%'}">
                        {{ uploadPercent + '%' }}
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>
<style lang="scss" scoped>
@use "../../../sass/mixins" as *;

.mass-uploader {
    &__record {
        background: var(--bg-darker);
        box-shadow: var(--block-box-shadow);
        border: 1px solid var(--border-color);
        position: relative;
        padding: 1em;
        border-radius: var(--border-radius-big);

        &__added {
            font-size: 1.25em;
            display: flex;
            align-items: center;
            gap: .5em;
            margin: -.5em 0;

            &__check {
                background: #2baf2b;
                color: #fff;
                padding: .25em;
                height: 1em;
                line-height: 1em;
                border-radius: var(--border-radius-small);
            }

            &__title {
                text-decoration: none;
                display: flex;
                align-items: center;
                gap: .5em;
                @include hover();
            }

            &__picture {
                height: 2em;
                aspect-ratio: 4/3;
                background-size: cover;
                background-position: center;
                border-radius: var(--border-radius-small);
            }

            &__edit {
                @include hover();
                margin-left: auto;
            }
        }

        &__top {
            display: flex;
            width: 100%;
            gap: var(--col-margin);

            &__left {
                flex: 1;
            }

            &__inner {
                align-items: center;
            }
        }

        &__picture {
            aspect-ratio: 4 / 3;
            height: 9.5em;
            background-size: cover;
            margin: 1em 0;
        }

        &__buttons {
            margin-top: 1.75em;
        }

        &__toggle-buttons {
            display: flex;
            font-size: .875em;
            white-space: nowrap;
            margin: 3em 0 0;
        }
    }
}
</style>
<script setup lang="ts">
import { computed, ref, useTemplateRef, watch } from "vue";
import { RecordsUploadData, useRecordForm } from "@/composables/record-form";
import { MultipleRecordsResponseItem } from "../MassUploader.vue";
import DateSelect from "@/components/DateSelect.vue";
import { useChannelsStore } from "@/stores/channels";
import { getAdditionalNames, getDisplayName } from "@/utils/channels";
import { useProgramsStore } from "@/stores/programs";
import TypeSelect from "@/components/record-form/TypeSelect.vue";
import Response from "@/components/Response.vue";
import SimilarModal from "@/components/record-form/SimilarModal.vue";
import { useCategoriesStore } from "@/stores/categories";
import { autocompleteOptions } from "@/utils/autocomplete";
import InputContainer from "@/components/InputContainer.vue";
import Preloader from "@/components/Preloader.vue";
import PreviewModal from "@/components/record-form/PreviewModal.vue";

const props = defineProps<{
    record: MultipleRecordsResponseItem,
    isRadio: boolean,
    files?: string[]
}>();
const emit = defineEmits<{
    (e: 'remove'): void,
    (e: 'similar', similar: Models.Record[]): void
}>();

const startParams: Partial<RecordsUploadData> = {
    title: props.record.title,
    description: props.record.description,
    record: {
        url: props.record.player,
        duration: props.record.duration,
        own_code: true,
        code: props.record.code,
        thumbnails: props.record.thumbnails,
        thumbnail_url: props.record.thumbnails ? props.record.thumbnails[0] : null,
        upload: !!props.record.file,
    }
}

const categoriesStore = useCategoriesStore();
const {
    data,
    loading,

    parseTitle,
    titleLocked,

    save,
    saving,
    setSaveCallback,
    errors,

    similar,
    markSimilarAsChecked,

    response,

    //uploadFile,
    setUploadFile,
    isUploadingFile,
    uploadPercent,
} = useRecordForm(startParams, null, false);

if (props.record.upload) {
    setUploadFile(props.record.upload);
}

parseTitle();
if (props.record.file) {
    data.value.uploaded_file_url = props.record.file;
} else {
    data.value.uploaded_file_url = props.files.find(file => file.replace('.mp4', '') === data.value.title);
    console.log(data.value.uploaded_file_url, data, props.files);
}

const saveRecord = () => {
    data.value.record.upload = data.value.record.uploaded_file_url?.length;
    save();
}

const remove = () => {
    emit('remove');
}

const addedRecord = ref<Models.Record>();
setSaveCallback((record: Models.Record, hasErrors: boolean) => {
    if (!hasErrors) {
        addedRecord.value = record;
    }
});

const channelsStore = useChannelsStore();
const programsStore = useProgramsStore();

const channelOptions = computed(() => {
    return (props.isRadio ? channelsStore.radioStations : channelsStore.channels).map(channel => {
        const names = getAdditionalNames(channel);
        return {
            id: channel.id,
            text: channel.name,
            fullText: `${getDisplayName(channel)}${names?.length ? ` (${names})` : ''}`,
        }
    });
});
const programOptions = computed(() => {
    return (programsStore.programs[data.value.channel.id] || []).map(program => {
        return {
            id: program.id,
            text: program.name,
        }
    });
});

const similarRef = useTemplateRef<typeof SimilarModal>('similarModal');
watch(similar, (_similar) => {
    if (_similar.length) {
        similarRef.value.show();
    }
})
const previewRef = useTemplateRef<typeof PreviewModal>('previewModal');

const regionsAutocompleteOptions = autocompleteOptions('', () => {
    return route('records.autocomplete.regions', {country: data.value.advertising.country});
});
const countriesAutocompleteOptions = autocompleteOptions('', route('records.autocomplete.countries'));
const brandsAutocompleteOptions = autocompleteOptions('', () => {
    return route('records.autocomplete.commercials-brands', {advertising_type: data.value.advertising.type});
});

const categoriesAutocompleteOptions = autocompleteOptions('', () => {
    return route('records.autocomplete.commercials-categories', {advertising_type: data.value.advertising.type});
});

const createTagOptions = {
    tags: true,
    createTag: (params) => {
        return {
            id: -1 * Math.round(Math.random() * 10000000),
            text: params.term,
            newOption: true
        }
    },
    templateResult: (data) => {
        return data.fullText || data.text;
    },
    matcher: (query, option) => {
        if (!query.term) {
            return option;
        }

        return option.text?.toLocaleLowerCase().includes(query.term.toLocaleLowerCase()) || option.fullText?.toLocaleLowerCase().includes(query.term.toLocaleLowerCase()) ? option : null;
    }
}

const _route = route;
</script>
