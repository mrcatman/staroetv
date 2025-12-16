<template>
    <div class="form record-form">

        <Preloader v-if="loading || saving || isUploadingFile"/>
        <Response :data="response" v-if="!inModal"/>

        <div class=" form__content record-form__content" v-if="data">
            <div class="input-container input-container--vertical " v-show="data.source_type !== 'local'">
                <label
                    class="input-container__label">{{ data.own_code ? 'Код для вставки Iframe плеера' : (isRadio ? 'Вставьте ссылку на аудиозапись' : 'Вставьте ссылку на видеозапись') }}</label>
                <div class="input-container__inner">
                    <div class="input-container__element-outer">
                        <input :disabled="loadingInfo" v-if="!data.record.own_code" class="input" v-model="data.record.url" :placeholder="isRadio ? 'Soundcloud либо прямая ссылка' : 'ВК либо Youtube'"/>
                        <textarea v-else class="input textarea" v-model="data.record.code"/>
                        <div class="input-container__toggle-buttons">
                            <a class="input-container__toggle-button" v-if="!data.record.own_code"
                               @click="data.own_code = true">Ввести код плеера вручную
                            </a>
                            <a class="input-container__toggle-button" v-else @click="data.record.own_code = false">
                                Вставить ссылку с {{ isRadio ? 'аудиохостинга' : 'видеохостинга' }}
                            </a>
                        </div>
                    </div>
                    <span class="input-container__message">
                        {{ errors.source_path ?? errors.url ?? errors.code }}
                    </span>
                </div>
                <div class="record-form__select-file__or">или</div>

                <label class="button button--big record-form__select-file__upload">
                    <input :accept="isRadio ? 'audio/*' : 'video/*'" ref="files" type="file" @change="onFileInputChange"
                           style="display: none"/>
                    Выберите файл для загрузки
                </label>
            </div>

            <div class="horisontal-delimiter"></div>

            <div class="input-container input-container--vertical">
                <label class="input-container__label">Тип записи</label>
                <div class="radio-buttons radio-buttons--tabs">
                    <label class="radio-button radio-button--tabs">
                        <input type="radio" v-model="data.type" name="type" value="programs" />
                        <div class="radio-button--tabs__variant">Передача</div>
                    </label>
                    <label class="radio-button radio-button--tabs">
                        <input type="radio" v-model="data.type" name="type" value="interprogram"/>
                        <div class="radio-button--tabs__variant">Заставка, анонс и т.д.</div>
                    </label>
                    <label class="radio-button radio-button--tabs">
                        <input type="radio" v-model="data.type" name="type" value="advertising"/>
                        <div class="radio-button--tabs__variant">Рекламный ролик</div>
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


            <div class="input-container input-container--vertical" v-if="!startParams?.channel_id" v-show="['programs', 'interprogram'].includes(data.type)">
                <label class="input-container__label">{{ isRadio ? 'Радиостанция' : 'Канал' }}</label>
                <div class="input-container__inner">
                    <channel-select
                        v-model:channel="data.channel"
                        :is-radio="isRadio"
                    >
                        <label class="input-container input-container--checkbox">
                            <input type="checkbox" v-model="data.channel.unknown">
                            <div class="input-container--checkbox__element"></div>
                            {{isRadio ? "Радиостанция неизвестна" : "Канал неизвестен"}}
                        </label>
                    </channel-select>
                    <span class="input-container__message">{{ errors.channel }}</span>
                </div>
            </div>
        </div>


        <div class="horisontal-delimiter"></div>
        <div class="input-container" v-if="record && canEditAll">
            <label class="input-container__label">Дата добавления</label>
            <div class="input-container__inner">
                <div class="input-container__element-outer">
                    <div class="input-container__overlay-outer">
                        <Datepicker v-model="data.original_added_at"></Datepicker>
                    </div>
                </div>
                <span class="input-container__message">{{ errors.original_added_at }}</span>
            </div>
        </div>

        <div class="input-container" v-if="record && canEditAll">
            <label class="input-container__label">Изменить автора на</label>
            <div class="input-container__inner">
                <div class="input-container__element-outer">
                    <div class="input-container__overlay-outer">
                        <select2 theme="default" :customOptions="usersAutocompleteOptions"
                                 v-model="data.author_id"></select2>
                    </div>
                </div>
                <span class="input-container__message">{{ errors.author_id }}</span>
            </div>
        </div>


        <div class="input-container">
            <label class="input-container__label">Заголовок</label>
            <div class="input-container__inner">
                <div class="input-container__element-outer">
                    <div class="input-container__overlay-outer">
                        <input class="input" v-model="data.title"/>
                    </div>
                </div>
                <span class="input-container__message">{{ errors.title }}</span>
            </div>
        </div>

        <!--
        <div class="input-container"
             v-if="!(params.is_interprogram || params.program_id | params.is_clip || params.is_advertising)">
            <label class="input-container__label">Программа</label>
            <div class="input-container__inner">
                <div class="input-container__element-outer">
                    <input class="input" v-model="data.program.name"
                           :disabled="(data.is_interprogram && !data.is_program_design) || data.is_clip || data.program.unknown || data.is_advertising  || data.is_other"/>
                    <div class="input-container__toggle-buttons">
                        <a class="input-container__toggle-button"
                           :class="{'input-container__toggle-button--active': data.program.unknown}"
                           @click="setUnknownProgram()">Программа неизвестна</a>
                        <a title="Заставки, анонсы и т.д." class="input-container__toggle-button"
                           :class="{'input-container__toggle-button--active': data.is_interprogram && !data.is_program_design}"
                           @click="setInterprogram()">Межпрограммное пространство</a>
                        <a class="input-container__toggle-button"
                           :class="{'input-container__toggle-button--active': data.is_advertising}"
                           @click="setAdvertising()">Рекламный ролик</a>
                        <a title="Заставки, титры и т.д." class="input-container__toggle-button"
                           :class="{'input-container__toggle-button--active': data.is_program_design}"
                           @click="setIsProgramDesign()">Оформление программы</a>
                        <a class="input-container__toggle-button"
                           :class="{'input-container__toggle-button--active': data.is_clip}" @click="setClip()">Клип</a>
                    </div>
                    <div class="autocomplete__items"
                         v-show="data.is_program_design || (!data.is_interprogram && !data.is_clip && !data.is_advertising && !data.program.unknown)">
                        <a @click="selectProgram(programItem)" class="autocomplete__item"
                           :class="{'autocomplete__item--selected': data.program.id === programItem.id}"
                           v-for="(programItem, $index) in filteredPrograms" :key="$index">
                            <span v-if="programItem.cover_picture" class="autocomplete__item__logo"
                                  :style="{backgroundImage: 'url('+programItem.cover_picture.url+')'}"></span>
                            <span class="autocomplete__item__name">{{ programItem.name }}</span>
                        </a>
                    </div>
                    <div v-if="data.is_interprogram && !data.is_other && !data.is_advertising"
                         class="record-form__interprogram-packages">
                        <div @click="data.interprogram_package_id = item.id" v-for="(item, $index) in designPackages"
                             :key="$index" class="record-form__interprogram-package"
                             :class="{'record-form__interprogram-package--selected': data.interprogram_package_id === item.id}">
                            <div class="record-form__interprogram-package__cover"
                                 :style="{backgroundImage: 'url('+(item.cover)+')'}"></div>
                            <div class="record-form__interprogram-package__name">
                                {{ item.name ? item.name : item.years_range }}
                            </div>
                        </div>
                        <div class="record-form__interprogram-package" @click="data.interprogram_package_id = null"
                             :class="{'record-form__interprogram-package--selected': data.interprogram_package_id === null}">
                            <div class="record-form__interprogram-package__cover"
                                 style="background-image: url('/pictures/unknown.png')"></div>
                            <div class="record-form__interprogram-package__name">Другое</div>
                        </div>
                    </div>
                </div>
                <span class="input-container__message">{{ errors.program }}</span>
            </div>
        </div>
        <div class="input-container" v-show="data.is_other">
            <label class="input-container__label">Тип</label>
            <div class="input-container__inner">
                <div class="input-container__element-outer" v-if="otherTypes.length > 0">
                    <select2 theme="default" :options="otherTypes" v-model="data.other_category_id"></select2>
                </div>
                <span class="input-container__message">{{ errors.other_category_id }}</span>
            </div>
        </div>

        <div class="record-form__player-container__outer"
             v-show="data.record.code || data.record.covers.length > 0 || data.program.cover_picture">
            <div class="record-form__player-container" v-html="data.record.code"></div>
            <div class="record-form__covers">
                <img class="record-form__cover" v-for="(cover, $index) in data.record.covers"
                     :class="{'record-form__cover--active': cover === data.cover}" @click="data.cover = cover"
                     :src="cover"/>
                <img class="record-form__cover" v-if="data.program.cover_picture"
                     :class="{'record-form__cover--active': data.program.cover_picture.url === data.cover}"
                     @click="data.record.thumbnail = data.program.cover_picture.url"
                     :src="data.program.cover_picture.url"/>
            </div>
        </div>
        <div class="input-container"
             v-show="!data.is_other && (data.is_interprogram || params.interprogram_package_id)">
            <label class="input-container__label">Тип</label>
            <div class="input-container__inner">
                <div class="input-container__element-outer" v-if="interprogramTypes.length > 0">
                    <select2 theme="default" :options="interprogramTypes" v-model="data.interprogram_type"></select2>
                </div>
                <span class="input-container__message">{{ errors.interprogram_type }}</span>
            </div>
        </div>
        <div class="input-container" v-show="data.is_advertising">
            <label class="input-container__label">Параметры рекламы</label>
            <div class="input-container__inner">
                <div class="input-container__element-outer">
                    <div class="record-form__inputs-group">
                        <div class="inputs-line">
                            <div class="inputs-line__item">
                                <div class="inputs-line__item__title">Рекламируется</div>
                                <input class="input" v-model="data.advertising_brand"/>
                            </div>
                            <div class="inputs-line__item" v-if="advertisingTypes.length > 0">
                                <div class="inputs-line__item__title">Тип</div>
                                <select2 theme="default" :options="advertisingTypes"
                                         v-model="data.advertising_type"></select2>
                            </div>
                        </div>
                        <br><br>
                        <div class="inputs-line">
                            <div class="inputs-line__item">
                                <div class="inputs-line__item__title">Город/регион (для местной рекламы)</div>
                                <input class="input" v-model="data.region"/>
                            </div>
                            <div class="inputs-line__item">
                                <div class="inputs-line__item__title">Страна (для зарубежной рекламы)</div>
                                <input class="input" v-model="data.country"/>
                            </div>
                        </div>
                    </div>

                </div>
                <span class="input-container__message">{{ errors.date }}</span>
            </div>
        </div>
        <div class="input-container">
            <label class="input-container__label">Дата выхода</label>
            <div class="input-container__inner">
                <div class="input-container__element-outer" v-if="loaded && !hideDateInputs">
                    <date-select
                        v-model="data.date"
                        :hide-day-and-month="data.is_advertising"
                    />
                    <br><br>
                    <div class="inputs-line" v-if="data.is_advertising || data.is_interprogram">
                        <div class="inputs-line__item">
                            <div class="inputs-line__item__title">Год начала показа</div>
                            <select2 theme="default" :options="yearOptions" v-model="data.date.year_start"></select2>
                        </div>
                        <div class="inputs-line__item">
                            <div class="inputs-line__item__title">Год окончания показа</div>
                            <select2 theme="default" :options="yearOptions" v-model="data.date.year_end"></select2>
                        </div>
                    </div>
                </div>
                <span class="input-container__message">{{ errors.date }}</span>
            </div>
        </div>
        <div class="input-container">
            <label class="input-container__label">Краткое описание</label>
            <div class="input-container__inner">
                <div class="input-container__element-outer">
                    <input class="input" v-model="data.short_description"/>
                    <div class="input-container__description">Уточните название сюжета, либо участников программы и
                        т.д.
                    </div>
                </div>
                <span class="input-container__message">{{ errors.short_description }}</span>
            </div>
        </div>
        <div class="input-container">
            <label class="input-container__label">Полное описание</label>
            <div class="input-container__inner">
                <div class="input-container__element-outer">
                    <textarea class="input input--textarea" v-model="data.description"></textarea>
                    <div class="input-container__description">Вы также можете указать таймкоды, по одному на строчку.
                        Пример:
                        <br>2:30 - В Чечне ...
                        <br>10:06 - Ельцин посетил ...
                    </div>
                </div>
                <span class="input-container__message">{{ errors.short_description }}</span>
            </div>
        </div>
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
            <a class="button" :class="{'button--light': inModal}" @click="save()">Сохранить</a>

            <Response :light="true" v-if="inModal" :data="response" />

            <div class="form__progress" v-if="isUploadingFile">
                <div class="form__progress__bar" :style="{width: uploadPercent + '%'}">
                    {{ uploadPercent + '%' }}
                </div>
            </div>
        </div>


    </div>
</template>
<style lang="scss">
.record-form {
    .select2-container {
        min-width: 100%;
    }

    &__covers {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        margin: 0 0 0 1em;
    }

    &__cover {
        height: auto;
        width: 10em;
        margin: .125em;
        border: 2px solid rgba(255, 255, 255, 0);
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

        iframe {
            min-height: 400px;
        }

        &__outer {
            display: flex;
            padding: 1em;
            margin: 1em 0 0;
            align-items: center;
            background: var(--bg-darker);
            border: 1px solid var(--border-color);
        }
    }

    &__interprogram-packages {
        background: var(--bg-darker);
        margin: 1em 0 0;
        display: flex;
        flex-wrap: wrap;
    }

    &__interprogram-package {
        width: 12em;
        cursor: pointer;
        padding: .5em;

        &:hover {
            background: rgba(0, 0, 0, 0.05);
        }

        &--selected {
            background: rgba(0, 0, 0, 0.1);
        }

        &__cover {
            height: 9em;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

    }
}
</style>
<script lang="ts" setup>
import { storeToRefs } from 'pinia';

// import Datepicker from 'vuejs-datepicker';

import Preloader from './Preloader.vue';
import Response from "./Response.vue";
import ChannelSelect from "./ChannelSelect.vue";
import DateSelect from "./DateSelect.vue";

// import {getYearOptions} from "@/utils/dates.js";

import { RecordsUploadData } from "@/composables/records-upload";
import { useRecordForm } from "@/composables/record-form";

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
    saving,
    loadingInfo,
    save,
    response,
    errors,
    setSaveCallback,

    setUploadFile,
    setUploadEndpoint,
    isUploadingFile,
    uploadPercent,
} = useRecordForm(props.startParams, props.record);

setUploadEndpoint(props.uploadEndpoint);

const onFileInputChange = (e: Event) => {
    setUploadFile((e.target as HTMLInputElement).files[0]);
}

setSaveCallback((record: Models.Record) => {
    if (props?.isRadio) {
        //this.response.text += `<a target=_blank href='${res.data.record.url}'>Перейти к радиозаписи</a>`;
    } else {
        //this.response.text += `<a target=_blank href='${res.data.record.url}'>Перейти к видеозаписи</a>`;
    }
    emit('save', record);

})


const usersAutocompleteOptions = {
    ajax: {
        method: 'GET',
        url: '/users/autocomplete',
        dataType: 'json',
        processResults: function (data) {
            return {
                results: data.data.users.map(user => {
                    return {
                        id: user.id,
                        text: user.username,
                    }
                }),
                pagination: {
                    more: data.data.users.length > 0
                }
            };
        },
    }
};

// otherTypes() {
//     const categories = (this.categories || []).filter(category => category.type === 'videos_other').map(category => {
//         return {id: category.id, text: category.name}
//     });
//     categories.unshift({
//         id: -1,
//         text: 'Другое'
//     });
//     return categories;
// },
// interprogramTypes() {
//     const categories = (this.categories || []).filter(category => category.type === 'interprogram').map(category => {
//         return {id: category.id, text: category.name}
//     });
//     categories.unshift({
//         id: -1,
//         text: 'Другое'
//     });
//     return categories;
// },
// advertisingTypes() {
//     const categories = (this.categories || []).filter(category => category.type === 'advertising').map(category => {
//         return {id: category.id, text: category.name}
//     });
//     categories.unshift({
//         id: -1,
//         text: 'Обычная'
//     });
//     return categories;
// },
//
// allChannelNames() {
//    let names = {};
//    this.channelsList.forEach(channel => {
//        names[channel.name] = channel.id;
//        if (channel.names) {
//            channel.names.forEach(channelName => {
//                names[channelName.name] = channelName.channel_id;
//            })
//        }
//    });
//    return names;
// },
//
// filteredPrograms() {
//     let programs = [];
//     if (this.data.program.name === '') {
//         programs =  this.programs;
//     } else {
//         let lowercaseName = this.data.program.name.toLowerCase();
//         programs =  this.programs.filter(program => program.name.toLowerCase().indexOf(lowercaseName) !== -1);
//     }
//     programs = programs.slice(0, 30);
//     return programs;
// },

</script>
