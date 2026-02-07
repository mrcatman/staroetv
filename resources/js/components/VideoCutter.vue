<template>
    <div class="video-cutter">
        <div class="form__preloader" v-show="isLoading"><img src="/resources/images/ajax.gif"></div>
        <snackbar ref="snackbar"></snackbar>

        <modal title="Просмотр видео" ref="previewModal">
            <div class="video-cutter__preview" v-if="recordToPreview">
                <video controls v-if="recordToPreview.use_own_player">
                    <source :src="recordToPreview.source_path" />
                </video>
                <div v-html="recordToPreview.embed_code" v-else class="video-cutter__preview__iframe-container"></div>
            </div>
        </modal>

        <div class="video-cutter__inner">
            <video ref="video" class="video-cutter__element">
                <source :src="cut.download_path">
            </video>
            <div class="video-cutter__slider">
                <div
                    @click="selectCut(index)"
                    :style="getResultStyle(item)"
                    :key="index"
                    class="video-cutter__timespan"
                    :class="{'video-cutter__timespan--active': currentCutIndex === index}"
                    v-for="(item, index) in cutResults"
                ></div>
                <vue-slider v-model="currentFrame" @change="setFrame" :min="0" :max="cut.frames" :interval="1" />
            </div>
            <div class="video-cutter__controls">
                <div class="video-cutter__controls__buttons">
                    <div class="video-cutter__controls__row">
                        <a
                            class="video-cutter__button"
                            :class="{'video-cutter__button--disabled': !cutResults[currentCutIndex]}"
                            @click="toCutStart()"
                        >
                            <span class="video-cutter__button__title">К началу ролика</span>
                            <i class="fa fa-step-backward"></i>
                        </a>
                        <a class="video-cutter__button" @click="changeFrame(-1)">
                            <span class="video-cutter__button__title">На 1 кадр назад</span>
                            <i class="fa fa-chevron-left"></i>
                        </a>
                        <a class="video-cutter__button" @click="playPause()">
                            <span class="video-cutter__button__title">Плей/пауза</span>
                            <i v-if="!isPlaying" class="fa fa-play"></i>
                            <i v-else class="fa fa-pause"></i>
                        </a>
                        <a class="video-cutter__button" @click="changeFrame(1)">
                            <span class="video-cutter__button__title">На 1 кадр вперед</span>
                            <i class="fa fa-chevron-right"></i>
                        </a>
                        <a
                            class="video-cutter__button"
                            :class="{'video-cutter__button--disabled': !cutResults[currentCutIndex]}"
                            @click="toCutEnd()"
                        >
                            <span class="video-cutter__button__title">К концу ролика</span>
                            <i class="fa fa-step-forward"></i>
                        </a>
                    </div>
                    <div class="video-cutter__controls__row">
                        <a class="video-cutter__button" @click="cutLeft()">
                            <span class="video-cutter__button__title">Назначить начальный кадр</span>
                            <i class="fa fa-quote-left"></i>
                        </a>
                        <a class="video-cutter__button" @click="newCut()">
                            <span class="video-cutter__button__title">Новый ролик</span>
                            <i class="fa fa-cut"></i>
                        </a>
                        <a class="video-cutter__button" @click="cutRight()">
                            <span class="video-cutter__button__title">Назначить конечный кадр</span>
                            <i class="fa fa-quote-right"></i>
                        </a>
                    </div>
                </div>
                <div class="video-cutter__controls__time">
                    <div class="video-cutter__frames">
                        {{ currentFrame }} / {{ cut.frames }}
                    </div>
                </div>
                <div class="video-cutter__save" v-if="!isFFmpegClientMode || FFmpegClientReady">
                    <a class="button" @click="save()">Сохранить</a>
                </div>
            </div>
            <a
                class="button video-cutter__client-mode"
                @click="startFFmpegClient()"
                v-if="!isFFmpegClientMode"
            >Перейти в клиентский режим</a>

            <div class="video-cutter__bottom">
                <div
                    class="video-cutter__percent"
                    :class="{'video-cutter__percent--loading': isMakingVideos}"
                >
                    <div
                        class="video-cutter__percent__inner"
                        :style="{'width': progressPercent * 100 + '%'}"
                    >
                        <span class="video-cutter__percent__text">{{ Math.floor(progressPercent * 100) }} %</span>
                    </div>
                </div>
                <div class="video-cutter__status">
                    {{ statusText }}
                </div>
            </div>
        </div>

        <div class="video-cutter__results">
            <label class="input-container input-container--checkbox">
                <input type="checkbox" v-model="setOldDate">
                <div class="input-container--checkbox__element"></div>
                <div class="input-container__label" v-if="video">Дата загрузки как у оригинального видео</div>
                <div class="input-container__label" v-else>Изменить дату загрузки на более старую</div>
            </label>
            <div class="video-cutter__video-info" v-if="!video || !video.channel">
                <div class="row row--with-inputs" v-if="channelsStore.channels.length">
                    <div class="input-container">
                        <label class="input-container__label">Канал</label>
                        <div class="input-container__inner">
                            <select2 v-model="channelId" :options="channelsOptions" />
                        </div>
                    </div>
                    <div class="input-container" v-if="!video">
                        <label class="input-container__label">Год</label>
                        <div class="input-container__inner">
                            <input type="number" class="input" v-model="year"/>
                        </div>
                    </div>
                </div>
            </div>

            <div
                @click="selectCut(index)"
                class="video-cutter__result"
                :class="{
                    'video-cutter__result--active': currentCutIndex === index,
                    'video-cutter__result--with-error': errors[index]
                }"
                v-for="(result, index) in cutResults"
                :key="index"
            >
                <a class="video-cutter__result__delete" @click="deleteCut(index)">Удалить</a>

                <select
                    v-model="result.data.is_advertising"
                    class="select-classic"
                >
                    <option :value="true">Реклама</option>
                    <option :value="false">Другое</option>
                </select>

                <date-select only-years range v-model="result.data" />
                <commercials-info-editor :record="result.data" />


                <div
                    class="video-cutter__result__additional"
                    v-if="result.data.is_advertising && adsByBrand[result.data.advertising_brand || ''] && adsByBrand[result.data.advertising_brand || ''].length > 0"
                >
                    <div class="video-cutter__related">
                        <span class="video-cutter__related__title">Похожие ролики</span>
                        <span
                            @click="showRelatedRecord(item)"
                            class="video-cutter__related__item"
                            v-for="(item, index) in adsByBrand[result.data.advertising_brand || '']"
                            :key="index"
                        >
                            {{ item.title }}
                        </span>
                    </div>
                </div>

                <div
                    class="video-cutter__result__additional"
                    v-if="!result.data.is_advertising && interprogramByType[getInterprogramSearchKey(result)] && interprogramByType[getInterprogramSearchKey(result)].length > 0"
                >
                    <div class="video-cutter__related">
                        <span class="video-cutter__related__title">Похожие ролики</span>
                        <span
                            @click="showRelatedRecord(item)"
                            class="video-cutter__related__item"
                            v-for="(item, index) in interprogramByType[getInterprogramSearchKey(result)]"
                            :key="index"
                        >
                            {{ item.title }}
                        </span>
                    </div>
                </div>

                <div class="video-cutter__result__response-container" v-if="errors[index]">
                    <div class="response response--light response--error">{{ errors[index] }}</div>
                </div>
            </div>
        </div>
    </div>
</template>
<style lang="scss">
.video-cutter {
    text-align: center;
    margin: 1em 0;
    display: flex;
    justify-content: space-between;
    position: relative;
    &__client-mode {
        margin: .5em 0 1.75em;
    }
    &__inner {
        width: 100%;
        max-width: 50%;
    }
    &__results {
        flex: 1;
        text-align: left;
        margin: 0 0 0 2.5em;
        border-left: 1px solid var(--border-color);
        padding: 0;
        max-height: 72.5vh;
        overflow: auto;
        font-size: .875em;
    }
    &__element {
        width: 100%;
    }
    .vue-slider-process {
        background: var(--primary);
    }
    &__controls {
        font-size: .875em;
        user-select: none;
        margin: .5em auto;
        border: 1px solid var(--border-color);
        padding: 1em;
        display: flex;
        justify-content: space-around;
        align-items: center;
        &__row {
            margin: 0 0 .5em;
        }

        &__buttons {
            margin: 0 0 -.5em;
        }
    }


    &__button {
        color: var(--text-lighter);
        font-size: 2em;
        padding: .25em 0;
        width: 2em;
        display: inline-block;
        background: var(--bg-darker);
        margin: 0 .125em;
        cursor: pointer;
        position: relative;
        &:hover {
            z-index: 10;
            filter: brightness(1.1);
        }
        &--disabled, &--disabled:hover {
            filter: brightness(1);
            background: var(--bg-darker);
            color:  var(--text-lightest);
            cursor: default;
            opacity: .5;
        }
        &__title {
            font-size: .65em;
            position: absolute;
            white-space: nowrap;
            top: -1em;
            left: calc(50% - .5em);
            z-index: 100000;
            background: var(--box-color-dark);
            color: var(--box-text-color-dark);
            padding: .25em .5em;
            display: none;
        }

        &:hover &__title {
            display: inline-block;
        }
    }
    &__slider {
        position: relative;
        padding: 0 0 1.25em;
    }
    &__timespan {
        background: var(--bg-darker-2);
        height: .5em;
        position: absolute;
        bottom: .25em;
        cursor: pointer;
        &--active {
            background: var(--primary)!important;

        }
        &:nth-of-type(2n) {
            background: var(--bg-darkest);
        }
    }

    &__result {
        padding: 0 1em;
        border-bottom: 1px solid var(--border-color);
        position: relative;
        .input-container__label {
            font-size: 1em;
        }
        &--active {
            background: var(--box-color-hover);
        }
        &--with-error {
            border: 1px solid #f00;
        }
        &__response-container {
            font-size: 1.25em;
            font-weight: bold;
            padding: .5em .5em 1em;
        }
        &__additional .row {
            height: 0;
            overflow: hidden;
            transition: all .25s;
        }

        &--active &__additional .row {
            height: 4em;
        }
        &__delete {
            position: absolute;
            background: var(--box-color-dark);
            color: var(--box-text-color-dark);
            padding: .25em .5em;
            font-size: 1.125em;
            right: .25em;
            top: .25em;
            z-index: 100;
            border-radius: var(--border-radius-small);
            cursor: pointer;
        }

    }
    &__frames {
        font-size: 2em;
    }
    &__percent {
        background: var(--bg-darker);
        padding: 0;
        font-size: 1.125em;
        overflow: hidden;
        &__inner {
            position: relative;
            font-size: 1.25em;
            background: var(--primary);
            color: var(--button-active-text);
            padding: .25em;
            overflow: hidden;
            white-space: nowrap;
            transition: all .25s;
        }
        &__text {
            z-index: 1;
            position: relative;
        }
    }


    &__percent--loading &__percent__inner:before {
        content: "";
        display: block;
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-image: linear-gradient(135deg, rgba(255, 255, 255, 0.15) 0%, rgba(255, 255, 255, 0.15) 25%, rgba(255, 255, 255, 0) 25%, rgba(255, 255, 255, 0) 50%, rgba(255, 255, 255, 0.15) 50%, rgba(255, 255, 255, 0.15) 75%, rgba(255, 255, 255, 0) 75%, rgba(255, 255, 255, 0) 100%);
        background-size: 2em 2em;
        z-index: 0;
        animation: videoCutterProgressBar 2s linear infinite;
    }

    &__status {
        background: var(--bg-darker-2);
        margin: .5em 0 0;
        padding: .5em;
        font-size: 1.125em;
    }
    &__related {
        font-size: 1.125em;
        margin: 0 .5em .5em;

        &__title {
            font-weight: bold;
        }

        &__item {
            cursor: pointer;
            border-bottom: 1px dashed;
            margin: 0 0 0 .5em;
        }
    }
    &__preview {
        width: 100%;
        position: relative;

        video, iframe {
            width: 640px;
            height: 360px;
        }
    }
}

@keyframes videoCutterProgressBar {
    from {
        background-position: 0 0;
    }
    to {
        background-position: -2em -2em;
    }
}
</style>
<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue';
import VueSlider from 'vue-slider-component'
import 'vue-slider-component/theme/antd.css'

import Snackbar from './Snackbar.vue';
import Modal from './Modal.vue';

import { useChannelsStore } from "../stores/channels";
import { useCategoriesStore } from "../stores/categories";
import { useDesignPackagesStore } from "../stores/design-packages";
import { useFFmpegClient } from "../composables/ffmpeg-client";
import CommercialsInfoEditor from "@/components/records-manager/CommercialsInfoEditor.vue";
import DateSelect from "../components/DateSelect.vue";

interface CutData {
    is_advertising: boolean;
    advertising_brand?: string;
    year?: number | null;
    interprogram_type?: number | string;
    interprogram_package_id?: number;
    short_description?: string;
    advertising_type?: number | string;
    region?: string;
    country?: string;
    year_start?: number;
    year_end?: number;
}

interface CutResult {
    start: number;
    end?: number;
    data: CutData;
    video_id?: number;
}

interface Category {
    id: number;
    name: string;
    type: string;
}

const props = defineProps<{
    cut: Models.VideoCut;
    channel?: Models.Channel;
    video?: Models.Record;
}>();

const fps = props.cut.fps > 60 ? 60 : props.cut.fps;
const frames = props.cut.frames;

const channelsStore = useChannelsStore();
const categoriesStore = useCategoriesStore();
const designPackagesStore = useDesignPackagesStore();



const video = ref<HTMLVideoElement | null>();
const snackbar = ref<InstanceType<typeof Snackbar>>();
const previewModal = ref<InstanceType<typeof Modal>>();

// State
const setOldDate = ref(false);
const isLoading = ref(false);

const isPlaying = ref(false);
const currentFrame = ref(0);
const currentCutIndex = ref(-1);
const cutResults = ref<CutResult[]>([]);
const isMakingVideos = ref(false);
const progressPercent = ref(0);
const errors = ref<Record<number, string>>({});

const year = ref(props.cut.year);



const videos = ref<any[]>([]);
const adsByBrand = ref<Record<string, Models.Record[]>>({});
const interprogramByType = ref<Record<string, Models.Record[]>>({});
const recordToPreview = ref<Models.Record | null>(null);
const restarted = ref(false);

const channelId = ref<number>(props.channel.id ?? props.cut.channel_id);


const getYear = computed(() => {
    if (year.value) {
        return year.value;
    }
    if (cutResults.value.length > 0) {
        return cutResults.value[cutResults.value.length - 1].data.year;
    }
    return null;
});

const channelsOptions = computed(() => {
    return channelsStore.channels.value.map(channel => ({
        id: channel.id,
        text: channel.name
    }));
});

const designPackages = computed(() => {
    return designPackagesStore.packages[channelId.value]
});

// Watchers
watch(() => channelId.value, () => {
    designPackagesStore.load(channelId.value);
});

// Methods
const showRelatedRecord = (record: Models.Record) => {
    recordToPreview.value = record;
    previewModal.value?.show();
};

const onChangeInterprogramPackageId = (record: CutResult) => {
    if (!record.data.interprogram_package_id || record.data.interprogram_package_id <= 0) {
        return;
    }
    if (!record.data.year) {
        const packageItem = designPackagesStore.find(record.data.interprogram_package_id);
        if (packageItem) {
            console.log(packageItem);
        }
    }
    loadInterprogramRecords(record);
};

const getInterprogramSearchKey = (record: CutResult): string => {
    return record.data.interprogram_package_id
        ? `${record.data.interprogram_package_id}_${record.data.interprogram_type}`
        : `${channelId.value}_${record.data.year}_${record.data.interprogram_type}`;
};

const loadInterprogramRecords = (record: CutResult) => {
    if (!record.data.interprogram_type || !channelId.value) {
        return;
    }
    const key = getInterprogramSearchKey(record);
    if (!interprogramByType.value[key]) {
        const data: any = { is_radio: false, is_interprogram: true };
        if (record.data.interprogram_package_id) {
            data.interprogram_package_id = record.data.interprogram_package_id;
        } else {
            if (record.data.year) {
                data.year = record.data.year;
            }
        }
        data.interprogram_type = record.data.interprogram_type;
        data.channel_id = channelId.value;
        $.post('/records/search', data).then((res: any) => {
            interprogramByType.value[key] = res.data.records.data;
        });
    }
};

const loadBrandRecords = (name: string) => {
    if (!adsByBrand.value[name]) {
        $.post('/records/search', { is_radio: false, is_advertising: true, search: name }).then((res: any) => {
            adsByBrand.value[name] = res.data.records.data;
        });
    }
};

const getNextBrand = (): string => {
    if (!props.video) {
        return "";
    }
    const matched = props.video.title?.match(/(.*?)\((.*?)\)(.*)/);
    if (matched && matched[3]) {
        const brands = matched[3].split(",").map(s => s.trim());
        const advertising = cutResults.value.filter(result => result.data.is_advertising);
        if (advertising.length > 0) {
            const lastBrand = advertising[advertising.length - 1].data.advertising_brand;
            if (lastBrand) {
                const index = brands.indexOf(lastBrand.trim());
                if (index !== -1 && brands[index + 1]) {
                    return brands[index + 1];
                }
            }
        } else {
            return brands[0];
        }
    }
    return "";
};

const deleteCut = (index: number) => {
    if (confirm("Вы уверены?")) {
        cutResults.value.splice(index, 1);
    }
};

const startMakingVideos = async (convertIndexes: number[]) => {
    restarted.value = false;
    isMakingVideos.value = true;
    progressPercent.value = 0;

    const makeVideo = async (index: number, videoIndex: number, dataOnly: boolean = false): Promise<boolean> => {
        if (isFFmpegClientMode.value) {
            let converted: Uint8Array | null = null;
            if (!dataOnly) {
                statusText.value = `Конвертация видео ${videoIndex} из ${indexes.length}`;
                const from = cutResults.value[index].start / fps;
                const to = (cutResults.value[index].end || frames) / fps;

                await ffmpeg.run(`-i source.mp4 -vcodec libx264 -acodec copy -threads 5 -ss ${from} -to ${to} output_${index}.mp4`);
                converted = await ffmpeg.read(`output_${index}.mp4`);
                videos.value[index] = converted;
            }

            const fd = new FormData();
            if (!dataOnly) {
                fd.append('set_old_date', setOldDate.value ? '1' : '0');
                fd.append('video', new Blob([converted], { type: "video/mp4" }));
                statusText.value = `Загрузка на сервер видео ${videoIndex} из ${indexes.length}`;
            } else {
                statusText.value = `Обновление информации о видео ${videoIndex} из ${indexes.length}`;
            }

            return new Promise<boolean>((resolve) => {
                $.ajax({
                    type: 'POST',
                    url: route('cut.make-video', props.cut.id, index),
                    data: fd,
                    processData: false,
                    contentType: false
                }).done((res: any) => {
                    console.log(res);
                    if (res.status) {
                        cutResults.value[index].video_id = res.data.video_id;
                        resolve(true);
                    } else {
                        snackbar.value?.show(res);
                        resolve(false);
                    }
                }).fail((xhr: any) => {
                    console.log(xhr);
                    const error = xhr.responseJSON;
                    snackbar.value?.show(error);
                    resolve(false);
                });
            });
        } else {
            if (!dataOnly) {
                statusText.value = `Конвертация на сервере видео ${videoIndex} из ${indexes.length}`;
            } else {
                statusText.value = `Обновление информации о видео ${videoIndex} из ${indexes.length}`;
            }

            return new Promise<boolean>((resolve) => {
                $.post('/cut/' + cut.value.id + '/make-video/' + index, {
                    data_only: dataOnly
                }).done((res: any) => {
                    if (res.status) {
                        cutResults.value[index].video_id = res.data.video_id;
                        resolve(true);
                    } else {
                        snackbar.value?.show(res);
                        resolve(false);
                    }
                }).fail((xhr: any) => {
                    const error = xhr.responseJSON;
                    snackbar.value?.show(error);
                    resolve(false);
                });
            });
        }
    };

    let hasErrors = false;
    let videoIndex = 1;
    const indexes: number[] = cutResults.value.map((_, i) => i);

    console.log(indexes, convertIndexes);

    for (const i in indexes) {
        if (isMakingVideos.value) {
            const index = indexes[i];
            if (!restarted.value) {
                const dataOnly = convertIndexes.indexOf(index) === -1;
                const status = await makeVideo(index, videoIndex, dataOnly);
                if (status) {
                    progressPercent.value += 1 / indexes.length;
                } else {
                    statusText.value = `Ошибка в видео ${videoIndex}`;
                    hasErrors = true;
                    progressPercent.value = 0;
                    isMakingVideos.value = false;
                }
                videoIndex++;
            }
        }
    }

    if (!hasErrors) {
        statusText.value = `Готово`;
        progressPercent.value = 1;
        isMakingVideos.value = false;
    }
};

const toCutStart = () => {
    const result = cutResults.value[currentCutIndex.value];
    if (result) {
        setFrame(result.start);
    }
};

const toCutEnd = () => {
    const result = cutResults.value[currentCutIndex.value];
    if (result) {
        setFrame(result.end || frames - 1);
    }
};

const save = () => {
    isLoading.value = true;
    cutResults.value.forEach(cutResult => {
        if (!cutResult.data.year) {
            if (props.video) {
                cutResult.data.year = props.video.year;
            } else {
                cutResult.data.year = getYear.value;
            }
        }
    });

    $.post('/cut/' + cut.value.id, {
        cuts: cutResults.value,
        year: getYear.value,
        channel_id: channelId.value
    }).done((res: any) => {
        snackbar.value?.show(res);
        isLoading.value = false;
        if (res.status) {
            errors.value = {};
            restarted.value = true;
            startMakingVideos(res.data.indexes);
        } else {
            errors.value = res.data.errors;
        }
    });
};

const selectCut = (index: number) => {
    if (currentCutIndex.value === index) {
        return;
    }
    currentCutIndex.value = index;
    currentFrame.value = cutResults.value[index].start;
    if (video.value) {
        video.value.currentTime = (currentFrame.value / fps);
    }
};

const getNextData = (): CutData => {
    if (cutResults.value.length === 0 || cutResults.value[cutResults.value.length - 1].data.is_advertising) {
        return {
            is_advertising: true,
            advertising_brand: getNextBrand(),
            year: props.video ? props.video.year : getYear.value,
        };
    } else {
        return {
            is_advertising: false,
            interprogram_type: cutResults.value[cutResults.value.length - 1].data.interprogram_type,
            interprogram_package_id: cutResults.value[cutResults.value.length - 1].data.interprogram_package_id,
            year: props.video ? props.video.year : getYear.value
        };
    }
};

const newCut = () => {
    if (cutResults.value.length === 0) {
        cutResults.value.push({
            start: 0,
            end: currentFrame.value,
            data: getNextData()
        });
        return;
    }

    const sortedCuts = cutResults.value
        .filter(item => (item.end || 0) < currentFrame.value)
        .sort((a, b) => (b.end || 0) - (a.end || 0));

    if (sortedCuts.length > 0) {
        const start = sortedCuts[0].end! + 1;
        cutResults.value.push({
            start,
            end: currentFrame.value,
            data: getNextData()
        });
    } else {
        cutResults.value[0].end = currentFrame.value - 1;
        cutResults.value.push({
            start: currentFrame.value,
            data: getNextData()
        });
    }
    currentCutIndex.value = cutResults.value.length - 1;
};

const getResultStyle = (item: CutResult) => {
    const style: any = {};
    style.left = ((item.start / frames) * 100) + '%';
    if (item.end) {
        style.width = (((item.end - item.start) / frames) * 100) + '%';
    } else {
        style.width = (100 - ((item.start / frames) * 100)) + '%';
    }
    return style;
};

const cutRight = () => {
    if (currentCutIndex.value === -1) {
        cutResults.value.push({
            start: 0,
            end: currentFrame.value,
            data: getNextData()
        });
        currentCutIndex.value = cutResults.value.length - 1;
    } else {
        cutResults.value[currentCutIndex.value].end = currentFrame.value;
    }
};

const cutLeft = () => {
    if (currentCutIndex.value === -1) {
        cutResults.value.push({
            start: currentFrame.value,
            end: frames,
            data: getNextData()
        });
        currentCutIndex.value = cutResults.value.length - 1;
    } else {
        cutResults.value[currentCutIndex.value].start = currentFrame.value;
    }
};

const setFrame = (frame: number) => {
    if (video.value) {
        video.value.currentTime = (frame / fps);
    }
};

const changeFrame = (count: number) => {
    if (video.value) {
        video.value.pause();
        video.value.currentTime += (count / fps);
        currentFrame.value = Math.floor(video.value.currentTime * fps);
    }
};

const playPause = () => {
    if (!video.value) {
        return;
    }

    if (!isPlaying.value) {
        video.value.play();
        isPlaying.value = true;
    } else {
        video.value.pause();
        isPlaying.value = false;
    }
}

onMounted(() => {

    if (video.value) {
        video.value.addEventListener('timeupdate', () => {
            currentFrame.value = Math.floor(video.value!.currentTime * fps);
        });
    }

    if (!props.video) {
        channelsStore.load();
    }
    categoriesStore.load();
    channelId.value && designPackagesStore.load(channelId.value);
});

const {
    init: initFFmpegClient,
    statusText: FFmpegClientStatusText,
    ready: FFmpegClientReady,
} = useFFmpegClient();
const isFFmpegClientMode = ref(false);
const startFFmpegClient = () => {
    isFFmpegClientMode.value = true;
    initFFmpegClient(props.cut.download_path);
}
</script>
