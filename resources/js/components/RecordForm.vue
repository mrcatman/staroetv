<template>
    <div class="form record-form">

        <Preloader v-if="loading || saving || isUploadingFile"/>
        <Response :data="response" v-if="!inModal"/>

        <div class="row row--align-start">
            <div class="col col--1-5">
                <div class=" form__content record-form__content" v-if="data">
                    <div class="row row--align-start">
                        <div class="col">
                            <div class="form__content">
                                <div class="input-container input-container--vertical"
                                     :class="{'input-container--with-errors': (!urlValid && !data.record.own_code) ?? (!codeValid && data.record.own_code) ?? errors.uploaded_file_id ?? errors.url ?? errors.code}">
                                    <label
                                        class="input-container__label">{{
                                            data.record.own_code ? 'Код для вставки Iframe плеера' : (isRadio ? 'Вставьте ссылку на аудиозапись' : 'Вставьте ссылку на видеозапись')
                                        }}</label>
                                    <div class="input-container__inner">
                                        <div class="input-container__element-outer">
                                            <input
                                                v-if="!data.record.own_code"
                                                :disabled="loadingInfo"
                                                class="input"
                                                v-model="data.record.url"
                                                :placeholder="isRadio ? 'Soundcloud либо прямая ссылка' : 'ВК либо Youtube'"
                                            />

                                            <textarea v-else class="input textarea" v-model="data.record.code"/>
                                            <span class="input-container__message">
                                                {{ errors.uploaded_file_id ?? errors.url ?? errors.code }}
                                            </span>
                                            <div class="input-container__toggle-buttons">
                                                <a class="input-container__toggle-button" v-if="!data.record.own_code"
                                                   @click="data.record.own_code = true">Ввести код плеера вручную
                                                </a>
                                                <a class="input-container__toggle-button" v-else
                                                   @click="data.record.own_code = false">
                                                    Вставить ссылку с {{ isRadio ? 'аудиохостинга' : 'видеохостинга' }}
                                                </a>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="record-form__select-file">
                                        <div class="record-form__select-file__or">или</div>

                                        <label class="button button--big record-form__select-file__upload">
                                            <input :accept="isRadio ? 'audio/*' : 'video/*'" ref="files" type="file"
                                                   @change="onFileInputChange"
                                                   style="display: none"/>
                                            Выберите файл для загрузки
                                        </label>
                                        <span v-if="uploadFile" class="record-form__select-file__name">
                                            {{ uploadFile.name }}
                                            <a class="record-form__select-file__remove" @click="setUploadFile()">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                        </span>

                                    </div>

                                </div>
                            </div>
                        </div>

                    </div>

                    <template v-if="sourceValid">
                        <div class="horisontal-delimiter"></div>

                        <div class="input-container input-container--vertical">
                            <label class="input-container__label">Заголовок</label>
                            <div class="input-container__inner">
                                <div class="input-container__element-outer">
                                    <div class="input-container__overlay-outer">
                                        <input class="input" :readonly="titleLocked" v-model="data.title"/>
                                        <a class="input-container__button">
                                            <i v-if="titleLocked" class="fa fa-edit" @click="titleLocked = false"></i>
                                            <i v-else class="fa fa-check" @click="titleLocked = true"></i>
                                        </a>
                                    </div>
                                </div>
                                <span class="input-container__message">{{ errors.title }}</span>
                            </div>
                        </div>

                        <div class="input-container input-container--vertical">
                            <label class="input-container__label">Тип записи</label>
                            <div class="radio-buttons radio-buttons--tabs">
                                <label class="radio-button radio-button--tabs">
                                    <input type="radio" v-model="data.type" name="type" value="programs"/>
                                    <div class="radio-button--tabs__variant">Передача</div>
                                </label>
                                <label class="radio-button radio-button--tabs">
                                    <input type="radio" v-model="data.type" name="type" value="interprogram"/>
                                    <div class="radio-button--tabs__variant">Заставка канала, анонс и т.д.</div>
                                </label>
                                <label class="radio-button radio-button--tabs">
                                    <input type="radio" v-model="data.type" name="type" value="advertising"/>
                                    <div class="radio-button--tabs__variant">Рекламный ролик</div>
                                </label>
                                <label class="radio-button radio-button--tabs">
                                    <input type="radio" v-model="data.type" name="type" value="program-design"/>
                                    <div class="radio-button--tabs__variant">Заставка передачи</div>
                                </label>
                                <label class="radio-button radio-button--tabs">
                                    <input type="radio" v-model="data.type" name="type" value="other"/>
                                    <div class="radio-button--tabs__variant">Прочее</div>
                                </label>
                                <!--
                                <label class="radio-button radio-button--tabs">
                                    <input type="radio" v-model="data.type" name="type" value="clip"/>
                                    <div class="radio-button--tabs__variant">Клип</div>
                                </label>
                                -->
                            </div>
                        </div>


                        <div class="input-container input-container--vertical"
                             :class="{'input-container--with-errors': errors.channel}"
                             v-if="!startParams?.channel_id"
                             v-show="['programs', 'interprogram', 'program-design'].includes(data.type)">
                            <label class="input-container__label">{{ isRadio ? 'Радиостанция' : 'Канал' }}</label>
                            <div class="input-container__inner">
                                <channel-select
                                    v-model:channel="data.channel"
                                    :is-radio="isRadio">
                                    <span class="input-container__message">{{ errors.channel }}</span>
                                </channel-select>
                            </div>
                        </div>

                        <template
                            v-if="['programs', 'program-design'].includes(data.type) && data.channel.name.length">
                            <div class="input-container input-container--vertical"
                                 :class="{'input-container--with-errors': errors.program}"
                            >
                                <label class="input-container__label">Программа</label>
                                <div class="input-container__inner">
                                    <program-select
                                        v-model:program="data.program"
                                        :channel="data.channel"
                                    />
                                    <span class="input-container__message">{{ errors.program }}</span>
                                </div>
                            </div>
                        </template>

                        <template
                            v-if="data.type === 'interprogram'">
                            <div class="input-container input-container--vertical"
                                 :class="{'input-container--with-errors': errors.interprogram}"
                            >
                                <label class="input-container__label">Тип</label>
                                <div class="input-container__inner">
                                    <div class="input-container__element-outer">
                                        <select2 theme="default" :options="interprogramCategories"
                                                 v-model="data.interprogram.type"></select2>
                                    </div>
                                    <span class="input-container__message">{{ errors.interprogram }}</span>
                                </div>
                            </div>
                        </template>

                        <template
                            v-if="data.type === 'other'">
                            <div class="input-container input-container--vertical"
                                 :class="{'input-container--with-errors': errors.other}"
                            >
                                <label class="input-container__label">Категория записи</label>
                                <div class="input-container__inner">
                                    <div class="input-container__element-outer">
                                        <select2
                                            theme="default"
                                            :options="otherCategories"
                                            v-model="data.other.category_id"
                                        />
                                    </div>
                                    <span class="input-container__message">{{ errors.other }}</span>
                                </div>
                            </div>
                        </template>

                        <template
                            v-if="data.type === 'advertising'">
                            <div class="horisontal-delimiter"></div>
                            <div class="warning-alert">
                                Данный раздел предназначен для отдельных рекламных роликов, для
                                загрузки рекламных блоков используйте "Заставка канала, анонс и т.д."
                            </div>

                            <div class="input-container input-container--vertical">
                                <label class="input-container__label">Что рекламируется</label>
                                <div class="input-container__inner">
                                    <div class="input-container__element-outer">
                                        <input class="input" v-model="data.advertising.brand"/>
                                        <div class="input-container__description" v-if="data.advertising.type == -1">Название бренда и товара (если есть)</div>
                                        <div class="input-container__description" v-else-if="data.advertising.type == 7">Имя политика или название партии</div>
                                        <div class="input-container__description" v-else-if="data.advertising.type == 8">Описание ролика</div>
                                    </div>
                                </div>

                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="input-container input-container--vertical">
                                        <label class="input-container__label">Тип рекламы</label>
                                        <div class="input-container__inner">
                                            <div class="input-container__element-outer">
                                                <select2 theme="default"
                                                         :options="advertisingTypes"
                                                         v-model="data.advertising.type"
                                                />
                                            </div>
                                            <div class="input-container__description">&nbsp;</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="input-container input-container--vertical">
                                        <label class="input-container__label">Город/регион</label>
                                        <div class="input-container__inner">
                                            <div class="input-container__element-outer">
                                                <select2
                                                    theme="default"
                                                    :customOptions="regionsAutocompleteOptions"
                                                    v-model="data.advertising.region"
                                                />
                                                <div class="input-container__description">Только для местной рекламы
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="input-container input-container--vertical">
                                        <label class="input-container__label">Страна</label>
                                        <div class="input-container__inner">
                                            <div class="input-container__element-outer">
                                                <select2
                                                    theme="default"
                                                    :customOptions="countriesAutocompleteOptions"
                                                    v-model="data.advertising.country"
                                                />
                                                <div class="input-container__description">Только для зарубежной
                                                    рекламы
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </template>


                        <div class="horisontal-delimiter"></div>

                        <div class="input-container input-container--vertical">
                            <label class="input-container__label">Дата выхода</label>
                            <div class="input-container__inner">
                                <div class="input-container__element-outer">
                                    <date-select
                                        v-model:date="data.date"
                                        :range="['advertising', 'interprogram', 'program-design'].includes(data.type) && data.date.range"/>
                                    <div v-if="['advertising', 'interprogram', 'program-design'].includes(data.type)"
                                         class="input-container__toggle-buttons">
                                        <a class="input-container__toggle-button" v-if="!data.date.range"
                                           @click="data.date.range = true">Указать временной промежуток показа
                                        </a>
                                        <a class="input-container__toggle-button" v-else
                                           @click="data.date.range = false">
                                            Указать конкретную дату
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="horisontal-delimiter"></div>
                        <div class="input-container input-container--vertical"
                             :class="{'input-container--with-errors': errors.short_description}"
                        >
                            <label class="input-container__label">Краткое описание</label>
                            <div class="input-container__inner">
                                <div class="input-container__element-outer">
                                    <input class="input" v-model="data.short_description"/>
                                    <div class="input-container__description" v-if="data.type === 'programs'">
                                        Уточните название сюжета, либо участники программы и т.д.
                                    </div>
                                    <div class="input-container__description" v-else-if="data.type === 'advertising'">
                                        Уточните слоган ролика
                                    </div>
                                </div>
                                <span class="input-container__message">{{ errors.short_description }}</span>
                            </div>
                        </div>
                        <div class="input-container input-container--vertical"
                             :class="{'input-container--with-errors': errors.description}"
                        >
                            <label class="input-container__label">Полное описание</label>
                            <div class="input-container__inner">
                                <div class="input-container__element-outer">
                                    <textarea class="input input--textarea" v-model="data.description"></textarea>
                                    <div class="input-container__description">Вы также можете указать таймкоды, по
                                        одному на строчку. Пример:
                                        <br>2:30 В Чечне ...
                                        <br>10:06 Ельцин посетил ...
                                    </div>
                                </div>
                                <span class="input-container__message">{{ errors.short_description }}</span>
                            </div>
                        </div>
                        <div class="input-container input-container--vertical"
                             :class="{'input-container--with-errors': errors.source}"
                        >
                            <label class="input-container__label">Источник записи</label>
                            <div class="input-container__inner">
                                <div class="input-container__element-outer">
                                    <input class="input" v-model="data.source"/>
                                </div>
                                <div class="input-container__description">
                                    Если это ваша собственная оцифровка/запись, оставьте поле пустым
                                </div>
                                <span class="input-container__message">{{ errors.source }}</span>
                            </div>
                        </div>
                    </template>


                    <template v-if="canEditAll">
                        <div class="horisontal-delimiter"></div>
                        <div class="input-container" v-if="record"
                             :class="{'input-container--with-errors': errors.original_added_at}"
                        >
                            <label class="input-container__label">Дата добавления</label>
                            <div class="input-container__inner">
                                <div class="input-container__element-outer">
                                    <div class="input-container__overlay-outer">
                                        <input class="input" type="date" v-model="data.original_added_at"/>
                                    </div>
                                </div>
                                <span class="input-container__message">{{ errors.original_added_at }}</span>
                            </div>
                        </div>

                        <div class="input-container" v-if="record"
                             :class="{'input-container--with-errors': errors.author_id}"
                        >
                            <label class="input-container__label">Изменить автора на</label>
                            <div class="input-container__inner">
                                <div class="input-container__element-outer">
                                    <div class="input-container__overlay-outer">
                                        <select2
                                            theme="default"
                                            :customOptions="usersAutocompleteOptions"
                                            v-model="data.author_id"
                                        />
                                    </div>
                                </div>
                                <span class="input-container__message">{{ errors.author_id }}</span>
                            </div>
                        </div>
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
                            :disabled="!sourceValid"
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
                </div>
            </div>
            <div class="col record-form__sidebar">
                <div class="col record-form__player-container__outer"
                     v-if="(data.record.code?.length && codeValid) || data.record.thumbnails.length">
                    <div class="record-form__player-container" v-if="data.record.code?.length && codeValid" v-html="data.record.code"></div>
                    <div class="record-form__thumbnails" v-show="data.record.thumbnails.length > 1">
                        <img class="record-form__thumbnail" v-for="(thumbnail, $index) in data.record.thumbnails"
                             :key="$index"
                             :class="{'record-form__thumbnail--active': thumbnail === data.record.thumbnail_url}"
                             @click="data.record.thumbnail_url = thumbnail"
                             :src="thumbnail"/>
                    </div>
                </div>
                <div class="record-form__tutorial">
                    <h3 class="record-form__tutorial__heading">Советы по добавлению видео</h3>
                    <ul class="record-form__tutorial__main">
                        <li>
                            Если материал не принадлежит вам, перезалейте на собственный аккаунт в соцсети или скачайте
                            и загрузите файл.
                            Желательно уточнить у загрузившего согласие на перезаливку материала на сайт.
                        </li>
                        <li>
                            Загрузка видео в плохом качестве, с ватермарками и т.д. допускается только в случае
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
    }

    &__select-file {
        display: flex;
        align-items: center;
        gap: 1em;

        &__name {
            font-size: 1.125em;
        }

        &__remove {
            cursor: pointer;
            margin-left: .25em;
            @include hover(true);
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
        align-items: center;
        gap: .25em;
    }

    &__thumbnail {
        height: 5em;
        width: 100%;
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
        position: relative;
        padding-top: 60%;

        iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

        &__outer {
            width: 100%;
            position: relative;
        }
    }


}
</style>
<script lang="ts" setup>
import {computed} from "vue";

// import Datepicker from 'vuejs-datepicker';

import Preloader from './Preloader.vue';
import Response from "./Response.vue";
import ChannelSelect from "./record-form/ChannelSelect.vue";
import ProgramSelect from "./record-form/ProgramSelect.vue";
import DateSelect from "./record-form/DateSelect.vue";

import {RecordsUploadData} from "@/composables/records-upload";
import {useRecordForm} from "@/composables/record-form";

import {useCategoriesStore} from "@/stores/categories";
import {autocompleteOptions} from "@/utils/autocomplete";

const props = defineProps<{
    uploadEndpoint: string,
    canUpload: boolean,
    canEditAll: boolean,
    inModal?: boolean,
    record?: Models.Record,
    isRadio?: boolean,
    startParams?: Partial<RecordsUploadData>
}>();

const emit = defineEmits<{ (e: 'save', record: Models.Record): void }>();

const {
    data,
    loading,
    loadingInfo,

    titleLocked,
    urlValid,
    codeValid,

    save,
    saving,
    setSaveCallback,

    response,
    errors,

    uploadFile,
    setUploadFile,
    setUploadEndpoint,
    isUploadingFile,
    uploadPercent,
} = useRecordForm(props.startParams, props.record);

setUploadEndpoint(props.uploadEndpoint);

const onFileInputChange = (e: Event) => {
    setUploadFile((e.target as HTMLInputElement).files[0]);
}

setSaveCallback((record: Models.Record, hasErrors: boolean) => {
    if (hasErrors) {
        window.scrollTo(0, 0);
    } else {
        if (props?.isRadio) {
            //this.response.text += `<a target=_blank href='${res.data.record.url}'>Перейти к радиозаписи</a>`;
        } else {
            //this.response.text += `<a target=_blank href='${res.data.record.url}'>Перейти к видеозаписи</a>`;
        }
        emit('save', record);
    }
})

const sourceValid = computed(() => {
    return (data.value.record.url.length && urlValid.value) || (data.value.record.code?.length && codeValid.value) || data.value.record.upload;
})

const categoriesStore = useCategoriesStore();

const otherCategories = computed(() => {
    const categories = (categoriesStore.categories || []).filter(category => category.type === 'videos_other').map(category => {
        return {id: category.id, text: category.name}
    });
    categories.unshift({
        id: -1,
        text: 'Другое'
    });
    return categories;
});

const interprogramCategories = computed(() => {
    const categories = (categoriesStore.categories || []).filter(category => category.type === 'interprogram').map(category => {
        return {id: category.id, text: category.name}
    });
    categories.unshift({
        id: -1,
        text: 'Другое'
    });
    return categories;
});


const advertisingTypes = computed(() => {
    const categories = (categoriesStore.categories || []).filter(category => category.type === 'advertising').map(category => {
        return {id: category.id, text: category.name}
    });
    categories.unshift({
        id: -1,
        text: 'Обычная'
    });
    return categories;
});

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

const regionsAutocompleteOptions = autocompleteOptions(props.record?.region, () => {
    return route('records.autocomplete.regions', {country: data.value.advertising.country});
});
const countriesAutocompleteOptions = autocompleteOptions(props.record?.country, route('records.autocomplete.countries'));


</script>
