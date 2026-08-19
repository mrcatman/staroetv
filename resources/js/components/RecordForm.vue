<template>
    <div class="form record-form">
        <Preloader v-if="loading || (saving && !isUploadingFile)
"/>
        <Response :data="response" v-if="!inModal"/>

        <similar-modal ref="similarModal" :similar="similar" @mark="markSimilarAsChecked"/>
        <div class="row row--align-start">
            <div class="col col--2">
                <div class=" form__content record-form__content" v-if="data">
                    <div class="row row--align-start">
                        <div class="col">
                            <div class="form__content">

                                <div class="input-container input-container--vertical"
                                     v-show="!data.record.id"
                                     :class="{'input-container--with-errors': (externalVideoError && !data.record.own_code) ?? (!codeValid && data.record.own_code) ?? errors.uploaded_file_path ?? errors.url ?? errors.code}">

                                    <div class="record-form__select-container">
                                        <div class="input-container__inner record-form__select-link"
                                             :class="{'input-container__inner--with-button': data.record.upload}">
                                            <div class="input-container__element-outer">
                                                <input
                                                    v-if="data.record.upload"
                                                    class="input"
                                                    readonly
                                                    :value="uploadFile?.name ?? 'Файл загружен на сервер сайта'"
                                                />
                                                <input
                                                    v-else-if="!data.record.own_code"
                                                    :disabled="loadingInfo"
                                                    class="input"
                                                    v-model="data.record.url"
                                                    :placeholder="`Вставьте ссылку (${isRadio ? 'Soundcloud либо прямая ссылка' : 'ВК, Youtube, Rutube'})`"
                                                />
                                                <textarea v-else placeholder="Код для вставки Iframe плеера"
                                                          class="input textarea" v-model="data.record.code"/>

                                                <!--
                                                <label
                                        class="input-container__label">{{
                                            data.record.own_code ? 'Код для вставки Iframe плеера' : (isRadio ? 'Вставьте ссылку на аудиозапись' : 'Вставьте ссылку на видеозапись')
                                        }}</label>
                                        -->
                                                <a v-if="data.record.upload" class="input-container__button"
                                                   @click="setUploadFile()">
                                                    <i class="fa fa-trash"></i>
                                                </a>
                                                <span class="input-container__message">
                                                    {{errors.uploaded_file_path ?? errors.url ?? errors.code ?? externalVideoError}}
                                                </span>
                                                <div v-if="!data.record.upload && !isRadio" class="input-container__toggle-buttons">
                                                    <a class="input-container__toggle-button"
                                                       v-if="!data.record.own_code"
                                                       @click="data.record.own_code = true">Ввести код плеера вручную
                                                    </a>
                                                    <a class="input-container__toggle-button" v-else
                                                       @click="data.record.own_code = false">
                                                        Вставить ссылку с {{isRadio ? 'аудиохостинга' : 'видеохостинга'}}
                                                    </a>
                                                </div>
                                            </div>

                                        </div>
                                        <div class="record-form__select-file" v-if="canUpload">
                                            <div class="record-form__select-file__or">или</div>
                                            <label class="button button--big record-form__select-file__upload">
                                                <input :accept="isRadio ? 'audio/*' : 'video/*'" ref="files" type="file"
                                                       @change="onFileInputChange"
                                                       style="display: none"/>
                                                Выберите файл для загрузки
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <input-container
                                    v-if="!data.record.upload && sourceValid && canUpload"
                                    checkbox
                                    label="Загрузить в хранилище сайта"
                                >
                                    <input type="checkbox" v-model="data.record.move_to_storage">
                                    <div class="input-container--checkbox__element"></div>
                                </input-container>

                            </div>
                        </div>
                    </div>

                    <input-container
                        class="record-form__duplicates"
                        v-if="duplicates?.length"
                        vertical
                        label="Дубликаты записи"
                    >
                        <div class="records-list">
                            <records-item v-for="record in duplicates" :key="record.id" :record="record"/>
                        </div>
                    </input-container>

                    <template v-if="sourceValid">
                        <div class="horisontal-delimiter"></div>

                        <input-container
                            autowidth
                            vertical
                            label="Тип записи"
                            :errors="errors.type"
                        >
                            <type-select v-model="data.type"/>
                        </input-container>

                        <input-container
                            vertical
                            withButton
                            label="Заголовок"
                            required
                            :errors="errors.title"
                        >
                            <input ref="title" class="input" :readonly="titleLocked" v-model="data.title"/>
                            <a @click="titleLocked = !titleLocked"
                               class="input-container__button">
                                <span class="tooltip">{{titleLocked ? 'Ввести название вручную' : 'Сбросить название'}}</span>
                                <i v-if="titleLocked" class="fa fa-edit"></i>
                                <i v-else class="fa fa-times"></i>

                            </a>

                            <template #description v-if="titleLocked">
                                Сгенерируется автоматически
                            </template>
                        </input-container>

                        <input-container
                            v-if="!startParams?.channel_id && ['programs', 'interprogram', 'program-design'].includes(data.type)"
                            vertical
                            :label="isRadio ? 'Радиостанция' : 'Канал'"
                            :errors="errors.channel"
                        >
                            <channel-select
                                v-model="data.channel"
                                :is-radio="isRadio">
                                <span class="input-container__message">{{  }}</span>
                            </channel-select>
                        </input-container>

                        <input-container
                            v-if="['programs', 'program-design'].includes(data.type) && data.channel.name.length"
                            vertical
                            label="Программа"
                            :errors="errors.channel">
                            <program-select
                                v-model:program="data.program"
                                :channel="data.channel"
                            />
                        </input-container>

                        <input-container
                            v-if="data.type === 'interprogram'"
                            vertical
                            label="Тип"
                            :errors="errors.interprogram"
                        >
                            <select2
                                theme="default"
                                :options="categoriesStore.interprogramTypes"
                                v-model="data.interprogram.type"
                            />
                        </input-container>

                        <input-container
                            v-if="data.type === 'other'"
                            vertical
                            label="Категория записи"
                            :errors="errors.other"
                        >
                            <select2
                                theme="default"
                                :options="categoriesStore.otherTypes"
                                v-model="data.other.category_id"
                            />
                        </input-container>

                        <template
                            v-if="data.type === 'advertising'">
                            <div class="horisontal-delimiter"></div>
                            <div class="warning-alert">
                                Данный раздел предназначен для отдельных рекламных роликов, для
                                загрузки рекламных блоков используйте "Заставка канала, анонс и т.д."
                            </div>
                        </template>

                        <div class="horisontal-delimiter"></div>

                        <input-container
                            vertical
                            label="Дата выхода"
                            :errors="errors.date"
                        >
                            <date-select
                                v-model="data.date"
                                :range="['advertising', 'interprogram', 'program-design'].includes(data.type) && data.date.range"
                            />
                            <template #toggleButtons v-if="['advertising', 'interprogram', 'program-design'].includes(data.type)">
                                <a class="input-container__toggle-button" v-if="!data.date.range"
                                   @click="data.date.range = true">Указать временной промежуток показа
                                </a>
                                <a class="input-container__toggle-button" v-else
                                   @click="data.date.range = false">
                                    Указать конкретную дату
                                </a>
                            </template>
                        </input-container>

                        <div class="horisontal-delimiter"></div>
                        <div class="row" v-if="data.type === 'advertising'">
                            <div class="col">
                                <input-container
                                    vertical
                                    label="Что рекламируется"
                                    required
                                    :errors="errors.advertising_brand">
                                    <select2
                                        theme="default"
                                        :customOptions="brandsAutocompleteOptions"
                                        v-model="data.advertising.brand"
                                    />

                                    <template #description>
                                        <template v-if="data.advertising.type == -1">
                                            Название рекламируемого бренда
                                        </template>
                                        <template v-else-if="data.advertising.type == 7">
                                            Имя политика или название партии
                                        </template>
                                        <template
                                            v-else-if="data.advertising.type == 8">Описание ролика
                                        </template>
                                    </template>
                                </input-container>
                            </div>

                            <div class="col">
                                <input-container
                                    vertical
                                    label="Категория"
                                    :errors="errors.advertising_category"
                                >
                                    <select2
                                        theme="default"
                                        :customOptions="categoriesAutocompleteOptions"
                                        v-model="data.advertising.category"
                                    />

                                    <template #description v-if="data.advertising.type == -1">
                                        Например, "Шоколадные батончики"
                                    </template>
                                </input-container>
                            </div>
                        </div>

                        <input-container
                            vertical
                            :label="data.type === 'advertising' ? 'Слоган / вариация сюжета' : 'Краткое описание'"
                            :errors="errors.short_description"
                        >
                            <input class="input" v-model="data.short_description"/>

                            <template #description>
                                <template v-if="data.type === 'programs'">
                                    Уточните название сюжета, участников программы и т.д.
                                </template>
                                <template v-else-if="data.type === 'advertising'">
                                    Слоган обычно идёт на последних секундах. Пример для рекламного ролика Bounty: "Райское наслаждение"
                                </template>
                            </template>
                        </input-container>

                        <div class="row" v-if="data.type === 'advertising'">
                            <div class="col">
                                <input-container
                                    vertical
                                    label="Тип рекламы"
                                    :errors="errors.advertising_type"
                                >
                                    <select2
                                        theme="default"
                                        :options="categoriesStore.advertisingTypes"
                                        v-model="data.advertising.type"
                                    />
                                    <template #description>&nbsp;</template>
                                </input-container>
                            </div>
                            <div class="col">
                                <input-container
                                    vertical
                                    label="Город/регион"
                                    :errors="errors.advertising_type"
                                >
                                    <select2
                                        theme="default"
                                        :customOptions="regionsAutocompleteOptions"
                                        v-model="data.advertising.region"
                                    />
                                    <template #description>Только для местной рекламы</template>
                                </input-container>
                            </div>
                            <div class="col">
                                <input-container
                                    vertical
                                    label="Страна"
                                    :errors="errors.country"
                                >
                                    <select2
                                        theme="default"
                                        :customOptions="countriesAutocompleteOptions"
                                        v-model="data.advertising.country"
                                    />
                                    <template #description>Только для зарубежной рекламы</template>
                                </input-container>
                            </div>
                        </div>

                        <input-container
                            vertical
                            label="Полное описание"
                            :errors="errors.description"
                        >
                            <textarea class="input input--textarea" v-model="data.description"></textarea>
                            <template #description>
                                <template v-if="data.type === 'programs'">
                                    можете указать таймкоды, по
                                    одному на строчку. Пример:
                                    <br>2:30 В Чечне ...
                                    <br>10:06 Ельцин посетил ...
                                </template>
                                <template v-else-if="data.type === 'advertising'">
                                    Полный текст (для облегчения поиска), либо информация об авторах и т.д.
                                </template>
                            </template>
                        </input-container>

                        <input-container
                            vertical
                            label="Источник"
                            :errors="errors.source"
                        >
                            <input class="input" v-model="data.source"/>
                            <template #description>
                                Если это ваша собственная оцифровка/запись, оставьте поле пустым
                            </template>
                        </input-container>


                        <template v-if="canEditAll && record">
                            <div class="horisontal-delimiter"></div>
                            <!--
                            <input-container
                                vertical
                                label="Дата добавления"
                                :errors="errors.original_added_at"
                            >
                                <input class="input" type="date" v-model="data.original_added_at"/>
                            </input-container>
                            -->
                            <input-container
                                vertical
                                label="Изменить автора на"
                                :errors="errors.author_id"
                            >
                                <select2
                                    theme="default"
                                    :customOptions="usersAutocompleteOptions"
                                    v-model="data.author_id"
                                />
                            </input-container>
                        </template>

                        <!--
                            <div class="input-container" v-if="loaded" v-show="!isRadio">
                                <label class="input-container__label">Обложка</label>
                                <div class="input-container__inner">
                                    <div class="input-container__element-outer">
                                        <picture-uploader :light="true" v-model="data.cover" :returnPath="true"/>
                                    </div>
                                    <span class="input-container__message">{{ errors.cover }}</span>
                                </div>
                            </div>
                            -->
                        <div class="form__bottom">
                            <button
                                class="button"
                                :class="{'button--light': inModal}"
                                @click.prevent="save()"
                                :disabled="!sourceValid || saving"
                            >
                                {{ data.id ? 'Сохранить' : 'Добавить' }}
                            </button>

                            <Response :light="true" v-if="inModal" :data="response"/>

                            <div class="form__progress" v-if="isUploadingFile">
                                <div class="form__progress__bar" :style="{width: uploadPercent + '%'}">
                                    {{ uploadPercent + '%' }}
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
            <div class="col record-form__sidebar" v-if="!inModal">
                <div class="col record-form__player-container" v-if="data.record.upload && data.record.source_hls">
                    <player-embed :record="{use_own_player: true, source_hls: data.record.source_hls}" />
                </div>
                <div class="col record-form__player-container"
                     v-else-if="(data.record.code?.length && codeValid) || data.record.thumbnails.length">
                    <player-embed v-if="data.record.code?.length && codeValid" :record="{embed_code: data.record.code}" />

                    <div class="record-form__thumbnails" v-show="data.record.thumbnails.length > 1">
                        <img class="record-form__thumbnail" v-for="(thumbnail, $index) in data.record.thumbnails"
                             :key="$index"
                             :class="{'record-form__thumbnail--active': thumbnail === data.record.thumbnail_url}"
                             @click="data.record.thumbnail_url = thumbnail; data.record.thumbnail_id = null"
                             :src="thumbnail"/>
                    </div>
                </div>
                <div class="record-form__tutorial">
                    <h3 class="record-form__tutorial__heading">Советы по добавлению роликов</h3>
                    <ul class="record-form__tutorial__main">
                        <li>
                            Если материал не принадлежит вам, перезалейте на собственный аккаунт в соцсети или скачайте
                            и загрузите файл.
                            Желательно уточнить у загрузившего согласие на перезаливку материала на сайт.
                        </li>
                        <li>
                            Загрузка роликов в плохом качестве, с ватермарками и т.д. допускается только в случае
                            отсутствия материала в лучшем виде.
                        </li>
                        <li>
                            Категорически не допускается загрузка фейков, "реконструкций" и т.д.
                        </li>
                        <li>
                            На данный момент принимаются только записи из стран бывшего СССР, а также каналов из других
                            стран на языках бывшего СССР.
                        </li>
                    </ul>
                </div>

            </div>
        </div>


    </div>
</template>
<style lang="scss">
@use "../../sass/mixins" as *;

.record-form {
    .select2-container {
        min-width: 100%;
    }

    &__duplicates {
        border-radius: 4px;
        border: 1px solid #ca0000;
        padding: 1em;
        box-sizing: border-box;
    }

    &__sidebar {
        position: sticky;
        align-self: start;
        top: 5.5em;
        display: flex;
        flex-direction: column;
        gap: var(--col-margin);
        border-left: 1px solid var(--border-color);
        margin-left: var(--col-margin);
        padding-left: var(--col-margin);
        @include mobile() {
            padding-top: var(--col-margin);
            padding-left: 0;
            border: none;
            position: unset;
            font-size: .9375em;
        }
    }


    &__select-container {
        display: flex;
        width: 100%;
        align-items: flex-start;
        gap: var(--col-margin);
        @include mobile() {
            flex-direction: column;
        }
    }

    &__select-file {
        display: flex;
        align-items: center;
        gap: 1em;

        &__name-container {
            overflow: hidden;
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        &__name {

            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

    }

    &__tutorial {
        display: flex;
        flex-direction: column;
        gap: var(--col-margin);

        &__heading {
            font-size: 1.3125em;
            margin: 0;
        }

        &__main {
            margin: 0;
            padding: 0 1em;
            font-size: 1.125em;

            li:not(:last-of-type) {
                margin-bottom: 1em;
            }
        }
    }

    &__thumbnails {
        margin-top: 1em;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: .25em;
    }

    &__thumbnail {
        height: 4em;
        border: 2px solid transparent;
        cursor: pointer;

        &--active {
            border: 2px solid var(--primary);
        }

        &:hover {
            border: 2px solid var(--border-color);
        }
    }

    &__player-container {
        width: 100%;
        position: relative;
    }


}
</style>
<script lang="ts" setup>
import { computed, ref, useTemplateRef, watch } from "vue";

// import Datepicker from 'vuejs-datepicker';

import Preloader from './Preloader.vue';
import Response from "./Response.vue";
import ChannelSelect from "./record-form/ChannelSelect.vue";
import ProgramSelect from "./record-form/ProgramSelect.vue";
import InputContainer from './InputContainer.vue';

import { RecordsUploadData } from "@/composables/records-upload";
import { useRecordForm } from "@/composables/record-form";

import { useCategoriesStore } from "@/stores/categories";
import { autocompleteOptions } from "@/utils/autocomplete";

import RecordsItem from "@/components/records/RecordsItem.vue";
import TypeSelect from "@/components/record-form/TypeSelect.vue";
import SimilarModal from "@/components/record-form/SimilarModal.vue";
import PlayerEmbed from "@/components/PlayerEmbed.vue";

const props = defineProps<{
    canEditAll?: boolean,
    inModal?: boolean,
    record?: Models.Record,
    startParams?: Partial<RecordsUploadData>
}>();

const isRadio = props.startParams?.is_radio ?? false;

const emit = defineEmits<{ (e: 'save', record: Models.Record): void }>();

const {
    data,
    loading,
    loadingInfo,

    titleLocked,
    codeValid,
    externalVideoError,

    save,
    saving,
    setSaveCallback,

    similar,
    duplicates,
    markSimilarAsChecked,

    response,
    errors,

    canUpload,
    uploadFile,
    setUploadFile,
    isUploadingFile,
    uploadPercent,
} = useRecordForm(props.startParams, props.record, !props.record);

const onFileInputChange = (e: Event) => {
    setUploadFile((e.target as HTMLInputElement).files[0]);
}

setSaveCallback((record: Models.Record, hasErrors: boolean) => {
    window.scrollTo(0, 0);
    emit('save', record);
});


const similarRef = useTemplateRef<typeof SimilarModal>('similarModal');
watch(similar, (_similar) => {
    if (_similar.length) {
        similarRef.value.show();
    }
})

const sourceValid = computed(() => {
    return !duplicates.value?.length && (data.value.record.url.length && !externalVideoError.value) || (data.value.record.code?.length && codeValid.value) || data.value.record.upload;
})

const titleRef = useTemplateRef<HTMLInputElement>('title');
watch(() => titleLocked.value, (locked) => {
    if (!locked) {
        titleRef.value?.focus();
    }
})

const categoriesStore = useCategoriesStore();

const usersAutocompleteOptions = {
    ajax: {
        method: 'GET',
        url: route('users.autocomplete'),
        dataType: 'json',
        processResults: ({data}) => {
            return {
                results: data.users.map(user => {
                    return {
                        id: user.id,
                        text: user.username,
                    }
                }),
                pagination: {
                    more: data.users.length > 0
                }
            }
        }
    }
};

const regionsAutocompleteOptions = autocompleteOptions(props.record?.region || '', () => {
    return route('records.autocomplete.regions', {country: data.value.advertising.country});
});
const countriesAutocompleteOptions = autocompleteOptions(props.record?.country || '', route('records.autocomplete.countries'));
const brandsAutocompleteOptions = autocompleteOptions(props.record?.advertising_brand || '', () => {
    return route('records.autocomplete.commercials-brands', {advertising_type: data.value.advertising.type});
});

const categoriesAutocompleteOptions = autocompleteOptions(props.record?.advertising_category || '', () => {
    return route('records.autocomplete.commercials-categories', {advertising_type: data.value.advertising.type});
});

</script>
