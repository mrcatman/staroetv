<template>
    <div class="video-cutter">
        <div class="form__preloader" v-show="isLoading"><img src="/pictures/ajax.gif"></div>
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
                <div @click="selectCut($index)" :style="getResultStyle(item)" :key="$index" class="video-cutter__timespan" :class="{'video-cutter__timespan--active': currentCutIndex === $index}" v-for="(item, $index) in cutResults"></div>
                <vue-slider v-model="currentFrame" @change="setFrame" :min="0" :max="cut.frames" :interval="1"></vue-slider>
            </div>
            <div class="video-cutter__controls">
                <div class="video-cutter__controls__buttons">
                    <div class="video-cutter__controls__row">
                        <a class="video-cutter__button" :class="{'video-cutter__button--disabled': !cutResults[currentCutIndex]}" @click="toCutStart()">
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
                        <a class="video-cutter__button" :class="{'video-cutter__button--disabled': !cutResults[currentCutIndex]}" @click="toCutEnd()">
                            <span class="video-cutter__button__title">К концу ролика</span>
                            <i class="fa fa-step-forward"></i>
                        </a>
                    </div>
                    <div class="video-cutter__controls__row">
                        <a class="video-cutter__button" @click="cutLeft()">
                            <span class="video-cutter__button__title">Назначить начальный кадр</span>
                            <i class="fa fa-quote-left"></i>
                        </a>
                        <a class="video-cutter__button" :class="{'video-cutter__button--disabled': cutResults.length === 0}" @click="newCut()">
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
                        {{currentFrame}} / {{this.cut.frames}}
                    </div>
                </div>
                <div class="video-cutter__save" v-if="!isClientMode || clientModeReady">
                    <a class="button" @click="save()">Сохранить</a>
                </div>
            </div>
             <a class="button video-cutter__client-mode" @click="initClientMode()" v-if="!isClientMode">Перейти в клиентский режим</a>

            <div class="video-cutter__bottom">
                <div class="video-cutter__percent" :class="{'video-cutter__percent--loading': isMakingVideos}">
                    <div class="video-cutter__percent__inner" :style="{'width': progressPercent * 100 + '%'}">
                        <span class="video-cutter__percent__text">{{parseInt(progressPercent * 100)}} %</span>
                    </div>
                </div>
                <div class="video-cutter__status">
                    {{statusText}}
                </div>
            </div>
        </div>
        <div class="video-cutter__results">
            <div class="video-cutter__video-info" v-if="!video || !video.channel">
                <div class="row row--with-inputs" v-if="channels.length > 0">
                    <div class="input-container">
                        <label class="input-container__label">Канал</label>
                        <div class="input-container__inner">
                            <select2 v-model="channel_id" :options="channelsOptions" />
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
            <div @click="selectCut($index)"  class="video-cutter__result" :class="{'video-cutter__result--active': currentCutIndex === $index, 'video-cutter__result--with-error': errors[$index]}" v-for="(result, $index) in cutResults" :key="$index">
                <a class="video-cutter__result__delete" @click="deleteCut($index)">Удалить</a>
                <div class="row row--with-inputs">
                    <div class="input-container">
                        <label class="input-container__label">Тип</label>
                        <div class="input-container__inner">
                            <select v-model="result.data.is_advertising" @change="onRecordTypeChange(result)" class="select-classic">
                                <option :value="true">Реклама</option>
                                <option :value="false">Другое</option>
                            </select>
                        </div>
                    </div>
                    <div class="input-container" v-if="!result.data.is_advertising">
                        <label class="input-container__label">Вид ролика</label>
                        <div class="input-container__inner">
                            <select @change="loadInterprogramRecords(result)" v-model="result.data.interprogram_type" class="select-classic">
                                <option :value="id" v-for="(type, id) in getTypes('interprogram')">
                                    {{type}}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row row--with-inputs" >
                    <div class="input-container" v-if="result.data.is_advertising">
                        <label class="input-container__label">Рекламируется<span class="input-container__required">*</span></label>
                        <div class="input-container__inner">
                            <input @change="loadBrandRecords(result.data.advertising_brand)" class="input" v-model="result.data.advertising_brand"/>
                        </div>
                    </div>
                    <div class="input-container" v-if="!result.data.is_advertising">
                        <label class="input-container__label">Пакет оформления</label>
                        <div class="input-container__inner">
                            <select @change="onChangeInterprogramPackageId(result)" v-model="result.data.interprogram_package_id" class="select-classic">
                                <option value="-1">-</option>
                                <option v-for="packageItem in interprogramPackages" :key="packageItem.id" :value="packageItem.id">{{packageItem.name || packageItem.years_range}}</option>
                            </select>
                        </div>
                    </div>
                    <div class="input-container">
                        <label class="input-container__label">Год выхода<span class="input-container__required">*</span></label>
                        <div class="input-container__inner">
                            <input @change="loadInterprogramRecords(result)" type="number" class="input" v-model="result.data.year"/>
                        </div>
                    </div>
                </div>
                <div class="row row--with-inputs" >
                    <div class="input-container" v-if="!result.data.is_advertising">
                        <label class="input-container__label">Описание</label>
                        <div class="input-container__inner">
                            <div class="input-container__element-outer">
                                <input class="input" v-model="result.data.short_description"/>
                                <div class="input-container__description">Например, анонсируемая передача</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="video-cutter__result__additional"  v-if="result.data.is_advertising">
                    <div class="row row--with-inputs" >
                        <label class="input-container__label">Тип рекламы</label>
                        <div class="input-container__inner">
                            <select v-model="result.data.advertising_type" class="select-classic">
                                <option :value="id" v-for="(type, id) in getTypes('advertising')">
                                    {{type}}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="video-cutter__result__additional" v-if="result.data.is_advertising">
                    <div class="row row--with-inputs" >
                        <div class="input-container">
                            <label class="input-container__label">Город (для местной рекламы)</label>
                            <div class="input-container__inner">
                                <input class="input" v-model="result.data.region"/>
                            </div>
                        </div>
                        <div class="input-container">
                            <label class="input-container__label">Страна (для зарубежной рекламы)</label>
                            <div class="input-container__inner">
                                <input class="input" v-model="result.data.country"/>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="video-cutter__result__additional">
                    <div class="row row--with-inputs" >
                        <div class="input-container">
                            <label class="input-container__label">Год начала показа</label>
                            <div class="input-container__inner">
                                <input type="number" class="input" v-model="result.data.year_start"/>
                            </div>
                        </div>
                        <div class="input-container">
                            <label class="input-container__label">Год конца показа</label>
                            <div class="input-container__inner">
                                <input type="number" class="input" v-model="result.data.year_end"/>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="video-cutter__result__additional" v-if="result.data.is_advertising && adsByBrand[result.data.advertising_brand] && adsByBrand[result.data.advertising_brand].length > 0" >
                    <div class="video-cutter__related">
                        <span class="video-cutter__related__title">Похожие ролики</span>
                        <span @click="showRelatedRecord(item)" class="video-cutter__related__item" v-for="(item, $index) in adsByBrand[result.data.advertising_brand]"  :key="$index">{{item.title}} </span>
                    </div>
                </div>
                <div class="video-cutter__result__additional" v-if="!result.data.is_advertising && interprogramByType[getInterprogramSearchKey(result)] && interprogramByType[getInterprogramSearchKey(result)].length > 0" >
                    <div class="video-cutter__related">
                        <span class="video-cutter__related__title">Похожие ролики</span>
                        <span @click="showRelatedRecord(item)" class="video-cutter__related__item" v-for="(item, $index) in interprogramByType[getInterprogramSearchKey(result)]" :key="$index">{{item.title}} </span>
                    </div>
                </div>
                <div class="video-cutter__result__response-container"  v-if="errors[$index]">
                    <div class="response response--light response--error">{{errors[$index]}}</div>
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
                border-radius: .25em;
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
<script>
    import VueSlider from 'vue-slider-component'
    import 'vue-slider-component/theme/default.css'
    import Snackbar from './Snackbar';
    import Modal from './Modal';

    window.downloadURL = (data, fileName) => {
        const a = document.createElement('a')
        a.href = data
        a.download = fileName
        document.body.appendChild(a)
        a.style.display = 'none'
        a.click()
        a.remove()
    };

    window.downloadBlob = (data, fileName, mimeType) => {
        const blob = new Blob([data], {
            type: mimeType
        })
        const url = window.URL.createObjectURL(blob)
        downloadURL(url, fileName)
        setTimeout(() => window.URL.revokeObjectURL(url), 1000)
    }

    export default {
        watch: {
            channel_id(newId) {
                this.getInterprogramPackagesList();
            }
        },
        computed: {
            getChannelId() {
                return this.channel ? this.channel.id : this.channel_id;
            },
            getYear() {
                if (this.year) {
                    return this.year;
                }
                if (this.cutResults.length > 0) {
                    return this.cutResults[this.cutResults.length - 1].year;
                }
                return null;
            },
            channelsOptions() {
                return this.channels.map(channel => {
                    return {
                        id: channel.id,
                        text: channel.name
                    }
                })
            }
        },
        mounted() {

            if (this.cut && this.cut.data) {
                this.cutResults = this.cut.data;
            }
            let video = this.$refs.video;
            video.addEventListener('timeupdate', (e) => {
                this.currentFrame = parseInt(video.currentTime * this.cut.fps);
            });
            if (!this.video) {
                this.getChannelsList();
            }
            this.getCategoriesList();
            this.getInterprogramPackagesList();
        },
        methods: {
            showRelatedRecord(record) {
                this.recordToPreview = record;
                this.$refs.previewModal.show();
            },
            onRecordTypeChange(record) {
                if (!record.data.is_interprogram && this.channels.length === 0) {
                    this.getChannelsList();
                }
            },
            onChangeInterprogramPackageId(record) {
                if (!record.data.interprogram_package_id || record.data.interprogram_package_id <= 0) {
                    return;
                }
                if (!record.data.year) {
                    let packageItem = this.interprogramPackages.filter(packageData => packageData.id == record.data.interprogram_package_id)[0];
                    if (packageItem) {
                        console.log(packageItem); //  record.data.year = packageItem.
                    }
                }
                this.loadInterprogramRecords(record)
            },
            getInterprogramSearchKey(record) {
                return record.data.interprogram_package_id ? (record.data.interprogram_package_id + "_" + record.data.interprogram_type) : (this.getChannelId + "_" + record.data.year + "_" + record.data.interprogram_type)
            },
            loadInterprogramRecords(record) {
                if (!record.data.interprogram_type || !this.getChannelId) {
                    return;
                }
                let key = this.getInterprogramSearchKey(record);
                if (!this.interprogramByType[key]) {
                    let data = {is_radio: false, is_interprogram: true};
                    if (record.data.interprogram_package_id) {
                        data.interprogram_package_id = record.data.interprogram_package_id;
                    } else {
                        if (record.data.year) {
                            data.year = record.data.year;
                        }
                    }
                    data.interprogram_type = record.data.interprogram_type;
                    data.channel_id = this.getChannelId;
                    $.post('/records/search', data).then(res => {
                        this.$set(this.interprogramByType, key, res.data.records.data);
                    })
                }
            },
            loadBrandRecords(name) {
                if (!this.adsByBrand[name]) {

                    $.post('/records/search', {is_radio: false, is_advertising: true, search: name}).then(res => {
                        this.$set(this.adsByBrand, name, res.data.records.data);
                    })
                }
            },
            async initClientMode() {
                this.isClientMode = true;
                let script = document.createElement('script');
                this.statusText = `Загрузка ffmpeg...`;
                script.onload = async () => {
                    let xhr = new XMLHttpRequest();
                    xhr.open('GET', this.cut.download_path, true);
                    xhr.responseType = 'arraybuffer';
                    xhr.onload = async (e) => {
                        if (xhr.status === 200) {
                            let video = new Uint8Array(xhr.response);
                            await ffmpeg.write("source.mp4", video);
                            this.ffmpeg = ffmpeg;
                            this.clientModeReady = true;
                            this.statusText = `Готово к запуску`;
                            console.log(ffmpeg, video);
                        }
                    };
                    const ffmpeg = FFmpeg.createFFmpeg({
                        log: true,
                        progress: ({ ratio }) => {
                            console.log('Progress', ratio);
                        },
                    });
                    window.ffmpeg = ffmpeg;
                    this.statusText = `Инициализация ffmpeg...`;
                    await ffmpeg.load();
                    this.statusText = `Загрузка файла видео...`;
                    xhr.send();
                };
                script.src = "https://unpkg.com/@ffmpeg/ffmpeg@0.8.3/dist/ffmpeg.min.js";
                document.head.appendChild(script);
            },
            getTypes(type) {
                let types = {};
                types[-1] = '-';
                if (!this.categories) {
                    return types;
                }
                this.categories.forEach(category => {
                    if (category.type === type) {
                        types[category.id] = category.name;
                    }
                });
                return types;
            },
            getInterprogramPackagesList() {
                if (!this.channel && !this.channel_id) {
                    return;
                }
                $.get('/channels/'+this.getChannelId+'/interprogram-packages').done(res => {
                    this.interprogramPackages = res.data.interprogram_packages;
                })
            },
            getCategoriesList() {
                $.get('/records/categories').then(res => {
                    this.categories = res.data.categories;
                })
            },
            getChannelsList() {
                $.get('/channels/ajax').then(res => {
                    this.channels = res.data.channels;
                })
            },
            getNextBrand() {
                if (!this.video) {
                    return "";
                }

                let matched = this.video.title.match(/(.*?)\((.*?)\)(.*)/);
                if (matched && matched[3]) {
                    let brands = matched[3].split(",").map(s => s.trim());
                    let advertising = this.cutResults.filter(result => result.data.is_advertising);
                    if (advertising.length > 0) {
                        let lastBrand = advertising[advertising.length - 1].data.advertising_brand;
                        if (lastBrand) {
                            let index = brands.indexOf(lastBrand.trim());
                            if (index !== -1) {
                                if (brands[index + 1]) {
                                    return brands[index + 1];
                                }
                            }
                        }
                    }  else {
                        return brands[0];
                    }
                }
                 return "";
            },
            deleteCut(index) {
                if (confirm("Вы уверены?")) {
                    this.cutResults.splice(index, 1);
                }
            },
            async startMakingVideos(indexes) {
                this.restarted = false;
                this.isMakingVideos = true;
                this.progressPercent = 0;
                const makeVideo = (index) => {
                    return new Promise(async (resolve, reject) => {
                        if (this.isClientMode) {
                            this.statusText = `Конвертация видео ${videoIndex} из ${indexes.length}`;
                            let from =  this.cutResults[index].start / this.cut.fps;
                            let to = this.cutResults[index].end / this.cut.fps;
                            let command = `-i source.mp4 -vcodec libx264 -acodec copy -threads 5 -ss ${from} -to ${to} output_${index}.mp4`;
                            await this.ffmpeg.run(command);
                            let converted = ffmpeg.read(`output_${index}.mp4`);
                            this.videos[index] = converted;
                            let fd = new FormData();
                            fd.append('video',  new Blob([converted], {type: "video/mp4"} ));
                            this.statusText = `Загрузка на сервер видео ${videoIndex} из ${indexes.length}`;
                            $.ajax({
                                type: 'POST',
                                url: '/cut/' + this.cut.id + '/make-video/' + index,
                                data: fd,
                                processData: false,
                                contentType: false
                            }).done(res => {
                                console.log(res);
                                if (res.status) {
                                    this.cutResults[index].video_id = res.data.video_id;
                                    resolve(true);
                                } else {
                                    this.$refs.snackbar.show(res);
                                    resolve(false);
                                }
                            }).fail((xhr) => {
                                console.log(xhr);
                                let error = xhr.responseJSON;
                                this.$refs.snackbar.show(error);
                                resolve(false);
                            });
                        } else {
                            this.statusText = `Конвертация на сервере видео ${videoIndex} из ${indexes.length}`;
                            $.post('/cut/' + this.cut.id + '/make-video/' + index).done(res => {
                                if (res.status) {
                                    this.cutResults[index].video_id = res.data.video_id;
                                    resolve(true);
                                } else {
                                    this.$refs.snackbar.show(res);
                                    resolve(false);
                                }
                            }).fail((xhr) => {
                                let error = xhr.responseJSON;
                                this.$refs.snackbar.show(error);
                                resolve(false);
                            });
                        }
                    });
                };
                let hasErrors = false;
                let videoIndex = 1;
                for (let i in indexes) {
                    if (this.isMakingVideos) {
                        let index = indexes[i];
                        if (!this.restarted) {
                            let status = await makeVideo(index);
                            if (status) {
                                this.progressPercent += 1 / indexes.length;
                            } else {
                                this.statusText = `Ошибка в видео ${videoIndex}`;
                                hasErrors = true;
                                this.progressPercent = 0;
                                this.isMakingVideos = false;
                            }
                            videoIndex++;
                        }
                    }
                }
                if (!hasErrors) {
                    this.statusText = `Готово`;
                    this.progressPercent = 1;
                    this.isMakingVideos = false;
                }
            },
           toCutStart() {
                let result = this.cutResults[this.currentCutIndex];
                if (result) {
                    this.setFrame(result.start);
                }
           },
            toCutEnd() {
                let result = this.cutResults[this.currentCutIndex];
                if (result) {
                    this.setFrame(result.end || this.cut.frames - 1);
                }
            },
           save() {
               this.isLoading = true;
               this.cutResults.forEach(cutResult => {
                   if (!cutResult.data.year) {
                       if (this.video) {
                           cutResult.data.year = this.video.year;
                       } else {
                           cutResult.data.year = this.getYear;
                       }
                   }
               });
               $.post('/cut/' + this.cut.id, {cuts: this.cutResults, year: this.getYear, channel_id: this.channel_id}).done(res => {
                   this.$refs.snackbar.show(res);
                   this.isLoading = false;
                   if (res.status) {
                       this.errors = {};
                       if (res.data.indexes.length > 0) {
                           this.restarted = true;
                           this.startMakingVideos(res.data.indexes);
                       }
                   } else {
                       this.errors = res.data.errors;
                   }
               })
           },
           selectCut(index) {
               if (this.currentCutIndex === index) {
                   return;
               }
               this.currentCutIndex = index;
               this.currentFrame = this.cutResults[index].start;
               this.$refs.video.currentTime = (this.currentFrame / this.cut.fps);
           },
            newCut() {
                if (this.cutResults.length === 0) {
                    return;
                }
                let sortedCuts = this.cutResults.filter(item => item.end < this.currentFrame).sort((a, b) => b.end - a.end);
                let start = this.cutResults;
                if (sortedCuts.length > 0) {
                    start = sortedCuts[0].end + 1;
                    this.cutResults.push({
                        start,
                        end: this.currentFrame,
                        data: {
                            is_advertising: true,
                            advertising_brand: this.getNextBrand(),
                            year: this.video ? this.video.year : this.getYear
                        }
                    });
                } else {
                    this.cutResults[0].end = this.currentFrame - 1;
                    this.cutResults.push({
                        start: this.currentFrame,
                        data: {
                            is_advertising: true,
                            advertising_brand: this.getNextBrand(),
                            year: this.video ? this.video.year : this.getYear
                        }
                    });
                }
                this.currentCutIndex = this.cutResults.length - 1;
            },
            getResultStyle(item) {
                let style = {};
                style.left = ((item.start / this.cut.frames) * 100) + '%';
                if (item.end) {
                    style.width = (((item.end - item.start) / this.cut.frames) * 100) + '%';
                } else {
                    style.width = (100 - ((item.start / this.cut.frames) * 100)) + '%';
                }
                return style;
            },
            cutRight() {
                if (this.currentCutIndex === -1) {
                    this.cutResults.push({
                        start: 0,
                        end: this.currentFrame,
                        data: {
                            is_advertising: true,
                            advertising_brand: this.getNextBrand(),
                            year: this.video ? this.video.year : this.getYear
                        }
                    });
                    this.currentCutIndex = this.cutResults.length - 1;
                } else {
                    this.cutResults[this.currentCutIndex].end = this.currentFrame;
                }
            },
            cutLeft() {
                if (this.currentCutIndex === -1) {
                    this.cutResults.push({
                        start: this.currentFrame,
                        end: this.cut.frames,
                        data: {
                            is_advertising: true,
                            brand: this.getNextBrand(),
                            year: this.video ? this.video.year : this.getYear
                        }
                    });
                    this.currentCutIndex = this.cutResults.length - 1;
                }  else {
                    this.cutResults[this.currentCutIndex].start = this.currentFrame;
                }
            },
            setFrame(frame) {
                 this.$refs.video.currentTime = (frame / this.cut.fps);
            },
            changeFrame(count) {
                this.$refs.video.pause();
                this.$refs.video.currentTime += (count / this.cut.fps);
                this.currentFrame = parseInt( this.$refs.video.currentTime * this.cut.fps);
            },
            playPause() {
                if (!this.isPlaying) {
                    this.$refs.video.play();
                    this.isPlaying = true;
                } else {
                    this.$refs.video.pause();
                    this.isPlaying = false;
                }
            }
        },
        data() {
            return {
                isLoading: false,
                cut: this.data,
                isPlaying: false,
                currentFrame: 0,
                currentCutIndex: -1,
                cutResults: [],
                isMakingVideos: false,
                progressPercent: 0,
                errors: {},
                channels: [],
                channel_id: this.data.channel_id,
                year: this.data.year,
                categories: [],
                interprogramPackages: [],
                ffmpeg: null,
                isClientMode: false,
                clientModeReady: false,
                videos: [],
                statusText: '',
                adsByBrand: {},
                interprogramByType: {},
                recordToPreview: null,
            }
        },
        props: {
            data: {
                type: Object,
                required: true
            },
            channel: {
                type: Object,
            },
            video: {
                type: Object
            },
        },
        components: {
            Modal,
            Snackbar,
            VueSlider
        }
    }
</script>
