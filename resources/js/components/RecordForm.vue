 <template>
    <div class="form record-form">
        <snackbar ref="snackbar"></snackbar>

        <div class="form__preloader" v-show="loading || fileUploadInfo.isUploading">
            <img src="/pictures/ajax.gif">
        </div>
        <Response :data="response" v-if="!inModal"/>
         <div class="input-container" v-show="!record || record.source_type !== 'local'">
            <label class="input-container__label" v-if="!isRadio">Ссылка на видео</label>
            <label class="input-container__label" v-else>Ссылка на аудиозапись</label>
            <div class="input-container__inner">
                <div class="input-container__element-outer">
                    <div class="input-container__overlay-outer">
                        <div class="input-container__disabled-overlay" v-if="data.record.own_code || data.record.use_own_player"></div>
                        <div class="input-container__preloader" v-show="isLoadingRecordInfo">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                    </div>
                    <input class="input" v-model="data.record.url"/>
                    <div class="input-container__description" v-if="isRadio">Soundcloud либо прямая ссылка</div>
                    <div class="input-container__description" v-else>ВК либо Youtube</div>
                    <div class="input-container__toggle-buttons">
                        <a class="input-container__toggle-button" :class="{'input-container__toggle-button--active': data.record.own_code}" @click="setOwnCode()">Ввести код для вставки вручную</a>
                        <a class="input-container__toggle-button" :class="{'input-container__toggle-button--active': data.record.use_own_player}" @click="setOwnPlayer()" v-if="canUpload">Загрузить на сайт</a>

                    </div>
                    <div class="record-form__player-container__outer" v-show="data.record.code || data.record.covers.length > 0 || data.program.cover_picture">
                        <div class="record-form__player-container" v-html="data.record.code"></div>
                        <div class="record-form__covers">
                            <img class="record-form__cover" v-for="(cover, $index) in data.record.covers" :class="{'record-form__cover--active': cover === data.cover}" @click="data.cover = cover" :src="cover" />
                            <img class="record-form__cover" v-if="data.program.cover_picture" :class="{'record-form__cover--active': data.program.cover_picture.url === data.cover}" @click="data.cover = data.program.cover_picture.url" :src="data.program.cover_picture.url" />
                        </div>
                    </div>
                </div>
                <span class="input-container__message">{{errors.url}}</span>
            </div>
        </div>
        <div class="input-container" v-show="data.record.own_code">
            <label class="input-container__label">Код для вставки</label>
            <div class="input-container__inner">
                <div class="input-container__element-outer">
                    <div class="input-container__overlay-outer">
                         <input class="input" v-model="data.record.code"/>
                    </div>
                </div>
                <span class="input-container__message">{{errors.code}}</span>
            </div>
        </div>
        <div class="input-container" v-if="data.record.use_own_player">
            <label class="input-container__label">Файл записи</label>
            <div class="input-container__inner">
                <div class="input-container__element-outer">
                    <div class="input-container__overlay-outer">
                        <input ref="files" type="file" @change="onFileInputChange"/>
                    </div>
                </div>
                <span class="input-container__message">{{errors.source_path}}</span>
            </div>
        </div>
        <div class="input-container" >
            <label class="input-container__label">Заголовок</label>
            <div class="input-container__inner">
                <div class="input-container__element-outer">
                    <div class="input-container__overlay-outer">
                        <input class="input" v-model="data.record.title"/>
                    </div>
                </div>
                <span class="input-container__message">{{errors.title}}</span>
            </div>
        </div>



        <div class="input-container" v-if="!params.channel_id">
            <label class="input-container__label" v-if="!isRadio">Канал</label>
            <label class="input-container__label" v-else>Радиостанция</label>
            <div class="input-container__inner">
                <div class="input-container__element-outer">
                    <div class="input-container__overlay-outer">
                        <div class="input-container__disabled-overlay" v-if="data.channel.unknown || data.is_other"></div>
                        <input class="input" @change="onChannelNameChange()" v-model="data.channel.name"/>
                    </div>
                    <div class="input-container__toggle-buttons">
                        <a class="input-container__toggle-button" :class="{'input-container__toggle-button--active': data.channel.unknown}" @click="setUnknownChannel()">{{isRadio ? "Радиостанция неизвестна" : "Канал неизвестен"}}</a>
                        <a class="input-container__toggle-button" :class="{'input-container__toggle-button--active': data.is_other}" @click="setIsOther()" title="Запись не относится к определенному каналу или содержит сразу несколько фрагментов">Другое</a>

                    </div>
                    <div class="autocomplete__items" v-show="!data.channel.unknown">
                        <a @click="selectChannel(channelItem)" class="autocomplete__item" :class="{'autocomplete__item--selected': data.channel.id === channelItem.id}" v-for="(channelItem, $index) in filteredChannels" :key="$index">
                            <span v-if="channelItem.logo" class="autocomplete__item__logo" :style="{backgroundImage: 'url('+channelItem.logo.url+')'}"></span>
                            <span class="autocomplete__item__name">{{channelItem.name}}</span>
                        </a>
                    </div>
                </div>
                <span class="input-container__message">{{errors.channel}}</span>
            </div>
        </div>
        <div class="input-container" v-if="!(params.is_interprogram || params.program_id | params.is_clip || params.is_advertising)">
            <label class="input-container__label">Программа</label>
            <div class="input-container__inner">
                <div class="input-container__element-outer">
                    <div class="input-container__overlay-outer">
                        <div class="input-container__disabled-overlay" v-if="(data.is_interprogram && !data.is_program_design) || data.is_clip || data.program.unknown || data.is_advertising  || data.is_other"></div>
                        <input class="input" v-model="data.program.name"/>
                    </div>
                    <div class="input-container__toggle-buttons">
                        <a class="input-container__toggle-button" :class="{'input-container__toggle-button--active': data.program.unknown}" @click="setUnknownProgram()">Программа неизвестна</a>
                        <a title="Заставки, анонсы и т.д." class="input-container__toggle-button" :class="{'input-container__toggle-button--active': data.is_interprogram}" @click="setInterprogram()">Межпрограммное пространство</a>
                        <a class="input-container__toggle-button" :class="{'input-container__toggle-button--active': data.is_advertising}" @click="setAdvertising()">Рекламный ролик</a>
                        <a title="Заставки, титры и т.д." class="input-container__toggle-button" :class="{'input-container__toggle-button--active': data.is_program_design}" @click="setIsProgramDesign()">Оформление программы</a>
                <!--        <a class="input-container__toggle-button" :class="{'input-container__toggle-button--active': data.is_clip}" @click="setClip()">Клип</a> -->
                    </div>
                    <div class="autocomplete__items" v-show="!data.is_program_design || (data.is_interprogram && !data.is_clip && !data.is_advertising && !data.program.unknown)">
                        <a @click="selectProgram(programItem)" class="autocomplete__item" :class="{'autocomplete__item--selected': data.program.id === programItem.id}" v-for="(programItem, $index) in filteredPrograms" :key="$index">
                            <span v-if="programItem.cover_picture" class="autocomplete__item__logo" :style="{backgroundImage: 'url('+programItem.cover_picture.url+')'}"></span>
                            <span class="autocomplete__item__name">{{programItem.name}}</span>
                        </a>
                    </div>
                    <div v-if="data.is_interprogram && !data.is_program_design && !data.is_other" class="record-form__interprogram-packages">
                        <div @click="data.interprogram_package_id = item.id" v-for="(item, $index) in interprogramPackages" :key="$index"  class="record-form__interprogram-package" :class="{'record-form__interprogram-package--selected': data.interprogram_package_id === item.id}">
                            <div class="record-form__interprogram-package__cover" :style="{backgroundImage: 'url('+(item.coverPicture ? item.coverPicture.url : '')+')'}"></div>
                            <div class="record-form__interprogram-package__name">{{item.name ? item.name : item.years_range}}</div>
                        </div>
                        <div class="record-form__interprogram-package" @click="data.interprogram_package_id = null"  :class="{'record-form__interprogram-package--selected': data.interprogram_package_id === null}">
                            <div class="record-form__interprogram-package__cover" style="background-image: url('/pictures/unknown.png')"></div>
                            <div class="record-form__interprogram-package__name">Другое</div>
                        </div>
                    </div>
                </div>
                <span class="input-container__message">{{errors.program}}</span>
            </div>
        </div>
        <div class="input-container" v-show="data.is_other">
            <label class="input-container__label">Тип</label>
            <div class="input-container__inner">
                <div class="input-container__element-outer" v-if="otherTypes.length > 0">
                    <select2 theme="default" :options="otherTypes" v-model="data.other_category_id"></select2>
                </div>
                <span class="input-container__message">{{errors.other_category_id}}</span>
            </div>
        </div>
        <div class="input-container" v-show="data.is_interprogram || params.interprogram_package_id">
            <label class="input-container__label">Тип</label>
            <div class="input-container__inner">
                <div class="input-container__element-outer" v-if="interprogramTypes.length > 0">
                    <select2 theme="default" :options="interprogramTypes" v-model="data.interprogram_type"></select2>
                </div>
                <span class="input-container__message">{{errors.interprogram_type}}</span>
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
                                <input class="input" v-model="data.advertising_brand" />
                            </div>
                            <div class="inputs-line__item" v-if="advertisingTypes.length > 0">
                                <div class="inputs-line__item__title">Тип</div>
                                <select2 theme="default" :options="advertisingTypes" v-model="data.advertising_type"></select2>
                            </div>
                        </div>
                        <br><br>
                        <div class="inputs-line">
                            <div class="inputs-line__item">
                                <div class="inputs-line__item__title">Город/регион (для местной рекламы)</div>
                                <input class="input" v-model="data.region" />
                            </div>
                            <div class="inputs-line__item">
                                <div class="inputs-line__item__title">Страна (для зарубежной рекламы)</div>
                                <input class="input" v-model="data.country" />
                            </div>
                        </div>
                    </div>

                </div>
                <span class="input-container__message">{{errors.date}}</span>
            </div>
        </div>
        <div class="input-container" >
            <label class="input-container__label">Дата выхода</label>
            <div class="input-container__inner">
                <div class="input-container__element-outer" v-if="dataIsSet && !hideDateInputs">
                     <div class="inputs-line">
                        <div class="inputs-line__item" v-if="!data.is_advertising">
                            <div class="inputs-line__item__title">День</div>
                            <select2 theme="default" :options="dayOptions" v-model="data.date.day"></select2>
                        </div>
                        <div class="inputs-line__item"  v-if="!data.is_advertising">
                            <div class="inputs-line__item__title">Месяц</div>
                            <select2 theme="default" :options="monthOptions" v-model="data.date.month"></select2>
                        </div>
                        <div class="inputs-line__item">
                            <div class="inputs-line__item__title">Год</div>
                            <select2 theme="default" :options="yearOptions" v-model="data.date.year"></select2>
                        </div>
                    </div>
                    <br><br>
                    <div class="inputs-line" v-if="data.is_advertising || data.is_interprogram">
                        <div class="inputs-line__item" >
                            <div class="inputs-line__item__title">Год начала показа</div>
                            <select2 theme="default" :options="yearOptions" v-model="data.date.year_start"></select2>
                        </div>
                        <div class="inputs-line__item" >
                            <div class="inputs-line__item__title">Год окончания показа</div>
                            <select2 theme="default" :options="yearOptions" v-model="data.date.year_end"></select2>
                        </div>
                    </div>
                </div>
                <span class="input-container__message">{{errors.date}}</span>
            </div>
        </div>
        <div class="input-container">
            <label class="input-container__label">Краткое описание</label>
            <div class="input-container__inner">
                <div class="input-container__element-outer">
                    <input class="input" v-model="data.short_description"/>
                    <div class="input-container__description">Уточните название сюжета, либо участников программы и т.д.</div>
                </div>
                <span class="input-container__message">{{errors.short_description}}</span>
            </div>
        </div>
        <div class="input-container">
            <label class="input-container__label">Полное описание</label>
            <div class="input-container__inner">
                <div class="input-container__element-outer">
                    <textarea class="input input--textarea" v-model="data.description"></textarea>
                    <div class="input-container__description">Вы также можете указать таймкоды, по одному на строчку. Пример:
                        <br>2:30 - В Чечне ...
                        <br>10:06 - Ельцин посетил ...
                    </div>
                </div>
                <span class="input-container__message">{{errors.short_description}}</span>
            </div>
        </div>
        <div class="input-container" v-if="dataIsSet">
            <label class="input-container__label">Обложка</label>
            <div class="input-container__inner">
                <div class="input-container__element-outer">
                    <picture-uploader :light="true" v-model="data.cover" :returnPath="true"/>
                </div>
                <span class="input-container__message">{{errors.cover}}</span>
            </div>
        </div>
        <div class="form__bottom">
            <a class="button" :class="{'button--light': inModal}" @click="save()">Сохранить</a>
            <Response :light="true" v-if="inModal" :data="response"></Response>
            <div class="form__progress" v-if="fileUploadInfo.isUploading">
                <div class="form__progress__bar" :style="{width: fileUploadInfo.percent + '%'}">{{fileUploadInfo.percent + '%'}}</div>
            </div>
        </div>

    </div>
</template>
<style lang="scss">
    .record-form {
        .input-container {
            margin: 0;
            padding: 1em;
            border-bottom: 1px solid var(--border-color);
        }
        .select2-container {
            min-width: 100%;
        }
        .form__bottom {
            padding: 1em 1em 0;
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
<script>
    import Response from "./Response";
    import Snackbar from "./Snackbar";
    let defaultData = {
        is_interprogram: false,
        is_clip: false,
        interprogram_package_id: null,
        cover: '',
        is_advertising: false,
        is_other: false,
        other_category_id: null,
        record: {
            own_code: false,
            title: '',
            url: '',
            id: null,
            code: null,
            covers: []
        },
        short_description: '',
        description: '',
        date: {
            year: -1,
            month: -1,
            day: -1,
            year_start: -1,
            year_end: -1,
        },
        program: {
            name: '',
            id: null,
            cover_picture: null,
            unknown: false,
        },
        channel: {
            name: '',
            id: null,
            unknown: false,
        }
    };
    export default {
        async mounted() {
            let yearOptions = this.yearOptions;
            await this.$nextTick();
            if (this.record) {
                this.data = {
                    is_interprogram: this.record.is_interprogram,
                    is_clip: this.record.is_clip,
                    is_advertising: this.record.is_advertising,
                    interprogram_package_id: this.record.interprogram_package_id ? parseInt(this.record.interprogram_package_id) : null,
                    interprogram_type: this.record.interprogram_type ? parseInt(this.record.interprogram_type) : null,
                    advertising_type: this.record.advertising_type,
                    advertising_brand: this.record.advertising_brand,
                    cover: this.record.cover,
                    region: this.record.region,
                    country: this.record.country,
                    own_code: this.record.own_code,
                    use_own_player: this.record.use_own_player,
                    is_other: !this.record.channel_id && !this.data.program_id && !this.record.is_advertising,
                    other_category_id: this.record.other_category_id,
                    record: {
                        title: this.record.title,
                        url: this.record.original_url,
                        id: null,
                        code: this.record.embed_code,
                        covers: []
                    },
                    short_description:  this.record.short_description,
                    description:  this.record.description,
                    date: {
                        year: this.record.year,
                        month: this.record.month,
                        day: this.record.day,
                        year_start: this.record.year_start,
                        year_end: this.record.year_end
                    },
                    program: {
                        name: this.record.program ? this.record.program.name : '',
                        id: this.record.program ? this.record.program.id : null,
                        cover_picture: this.record.program ? this.record.program.cover_picture : null,
                        unknown: !(this.record.program_id > 0) && !this.record.is_interprogram  && !this.record.is_clip  && !this.record.is_advertising,
                    },
                    channel: {
                        name: this.record.channel ? this.record.channel.name : '',
                        id: this.record.channel ? this.record.channel.id : null,
                        unknown: !(this.record.channel_id > 0),
                    }
                };
                if (this.record.channel && this.record.channel.id) {
                    this.loadPrograms();
                    if (this.record.is_interprogram) {
                        if (!this.isRadio) {
                            this.loadInterprogramPackages();
                        }
                    }
                }
                setTimeout(() => {
                    this.dataIsSet = true;
                }, 1000)
            } else {
                this.dataIsSet = true;
            }
            this.loadCategories();
            if (!this.channelsList || this.channelsList.length === 0) {
                this.loadChannels();
            }
        },
        components: {Snackbar, Response},
        props: {
            canUpload: {},
            inModal: {},
            channels: {},
            record: {},
            meta: {},
            params: {
                type: Object,
                required: false,
                default: () => {
                    return {}
                }
            }
        },
        methods: {
            uploadRecord() {
                return new Promise((resolve, reject) => {
                    let record = this.$refs.files.files[0];
                    if (!record) {
                        resolve();
                    }
                    let fd = new FormData();
                    fd.append('record', record);
                    this.fileUploadInfo.isUploading = true;
                    $.ajax({
                        xhr: () => {
                            let xhr = new window.XMLHttpRequest();
                            xhr.upload.addEventListener("progress",  (evt) => {
                                if (evt.lengthComputable) {
                                    let percentComplete = evt.loaded / evt.total;
                                    percentComplete = parseInt(percentComplete * 100);
                                    console.log(percentComplete);
                                    this.fileUploadInfo.percent = percentComplete;
                                }
                            }, false);
                            return xhr;
                        },
                        url: '/records/upload',
                        type: "POST",
                        processData: false,
                        data: fd,
                        contentType: false,
                    }).done(res => {
                        this.fileUploadInfo.isUploading = false;
                        console.log('done', res);
                        if (res.status) {
                            resolve(res.data);
                            this.fileUploadInfo.url = res.data.url;
                            this.fileUploadInfo.screenshot = res.data.screenshot;
                        } else {
                            reject();
                            this.$refs.snackbar.show(res);
                        }
                    }).fail(e => {
                        console.log('fail', e);
                        this.fileUploadInfo.isUploading = false;
                        this.$refs.snackbar.show({
                            status: 0,
                            text: e.responseJSON ? (e.responseJSON.message && e.responseJSON.message !== "" ? e.responseJSON.message : e.responseJSON.exception) : "Ошибка загрузки"
                        });
                        reject();
                    });
                })
            },
            onFileInputChange(e) {
                let file = e.target.files[0];
                if (!file) {
                    return;
                }
                this.needsUploadRecord = true;
            },
            onChannelNameChange() {
                if (this.data.channel.name === '') {
                    this.data.channel.id = null;
                }
            },
            async save() {
                let uploadData = null;
                if (this.needsUploadRecord) {
                    try {
                       await this.uploadRecord();
                       this.needsUploadRecord = false;
                    } catch (e) {
                        console.log(e);
                        return;
                    }
                }
                this.loading = true;
                this.data.is_radio = this.isRadio;
                let data = JSON.parse(JSON.stringify(this.data));
                data = {...data, ...this.params};
                if (this.params.is_interprogram) {
                    data.is_selected = true;
                }
                if (this.fileUploadInfo.url) {
                    data.record.source_path = this.fileUploadInfo.url;
                }
                if (this.fileUploadInfo.screenshot) {
                    data.record.original_cover = this.fileUploadInfo.screenshot;
                }
                $.post(this.record ? '/records/' + this.record.id + '/edit' : '/records/add', data).done(res => {
                    this.loading = false;
                    this.response = res;
                    this.errors = res.errors || {};
                    window.scrollTo(0, 0);
                    if (res.status) {
                        if (this.isRadio) {
                            this.response.text+= `<a target=_blank href='${res.data.record.url}'>Перейти к радиозаписи</a>`;
                        } else {
                            this.response.text+= `<a target=_blank href='${res.data.record.url}'>Перейти к видеозаписи</a>`;
                        }
                        this.$emit('save', res.data.record);

                        if (!this.record) {
                            this.data = JSON.parse(JSON.stringify(defaultData));
                            this.programs = [];
                            this.interprogramPackages = [];
                        }
                    }
                });
            },
            setClip() {
                this.data.is_clip = !this.data.is_clip;
                if (this.data.is_clip) {
                    this.data.is_advertising = false;
                    this.data.is_interprogram = false;
                    this.data.program.unknown = false;
                }
            },
            setAdvertising() {
                this.$set(this.data, 'is_advertising', !this.data.is_advertising);
                if (this.data.is_advertising) {
                    this.data.is_clip = false;
                    this.data.is_interprogram = false;
                    this.data.program.unknown = false;
                }
            },
            setIsProgramDesign() {
                this.$set(this.data, 'is_program_design', !this.data.is_program_design);
                this.data.is_interprogram = true;
            },
            setInterprogram() {
                this.data.is_interprogram = !this.data.is_interprogram;
                if (this.data.is_interprogram) {
                    this.data.is_advertising = false;
                    this.data.is_clip = false;
                    this.data.program.unknown = false;
                    if (this.data.channel.id) {
                        if (!this.isRadio) {
                            this.loadInterprogramPackages();
                        }
                    }
                }
            },
            setOwnPlayer() {
                this.$set(this.data.record, 'use_own_player', !this.data.record.use_own_player);
            },
            setOwnCode() {
                this.$set(this.data.record, 'own_code', !this.data.record.own_code);
            },
            setUnknownProgram() {
                this.data.program.unknown = !this.data.program.unknown;
                if (this.data.program.unknown) {
                    this.data.is_interprogram = false;
                    this.data.is_clip = false;
                    this.data.is_advertising = false;
                }
            },
            setIsOther() {
                if (!this.data.is_other) {
                    this.data.is_other = true;
                    this.data.is_advertising = false;
                    this.data.channel.unknown = false;
                    this.data.channel.id = null;
                    this.data.program.id = false;
                    this.data.program.id = null;
                } else {
                    this.data.is_other = false;
                }
            },
            setUnknownChannel() {
                this.data.channel.unknown = !this.data.channel.unknown;
            },
            async getRecordData() {
                let url = this.data.record.url;
                if (!url || url === '') {
                    return;
                }
                let youtubeData = url.match(/^.*((m\.)?youtu\.be\/|vi?\/|u\/\w\/|embed\/|\?vi?=|\&vi?=)([^#\&\?]*).*/);
                if (youtubeData && youtubeData.length === 4) {
                    let id = youtubeData[3];
                    let code = `<iframe width="560" height="315" src="https://www.youtube.com/embed/${id}" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>`;
                    this.data.record.id = id;
                    this.data.record.code = code;
                    this.data.record.covers = [];
                    ['0', '1', '2', '3', 'hqdefault'].forEach(frame => {
                        this.data.record.covers.push(`https://img.youtube.com/vi/${id}/${frame}.jpg`);
                    });
                    this.isLoadingRecordInfo = true;
                    $.post('/records/getinfo', {youtube_video_id: id}).done(async res => {
                        this.isLoadingRecordInfo = false;
                        if (res.status && res.data.youtube_response && res.data.youtube_response.items && res.data.youtube_response.items.length > 0) {
                            let record = res.data.youtube_response.items[0].snippet;
                            if (!this.dataIsSet) {
                                return;
                            }
                            let title = record.title;
                            this.data.record.title = title;
                            this.parseInfo(title);
                        }
                    })
                } else {
                    let id = null;
                    let vkData = url.match(/(.*?)vk.com\/video(.*?)([0-9-_]+)(.*?)/);
                    if (vkData && vkData[3].length > 1) {
                        id = vkData[3];
                    } else {
                        let vkData = url.match(/(.*?)video_ext.php\?oid=(.*?)&id=(.*?)&(.*?)/);
                        if (vkData) {
                            id = vkData[2] + '_' + vkData[3];
                        }
                    }
                    if (id) {
                        this.isLoadingRecordInfo = true;
                        $.post('/records/getinfo', {vk_video_id: id}).done(async res => {
                            this.isLoadingRecordInfo = false;
                            if (res.status && res.data.vk_response && res.data.vk_response.response && res.data.vk_response.response.items.length > 0) {
                                let record = res.data.vk_response.response.items[0];
                                this.data.record.id = id;
                                this.data.record.code = `<iframe width="560" height="315" src=${record.player} frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>`;
                                this.data.record.covers = [record.image[record.image.length - 1].url];
                                let title = record.title;
                                this.data.record.title = title;
                                this.parseInfo(title);
                            }
                        })
                    }
                }
            },
            async parseInfo(title) {
                let parsed = title.match(/((.*?){0,1}staroetv.su(.*?){0,1})?[\])\\/ ]{0,2}(.*?)\((.*?), (.*?)\)(.*)/);
                if (!parsed) {
                    let newParsed = title.match(/(.*?){0,1}staroetv.su(.*?){0,1} (.*?) - (.*?) \((.*?)\)(.*?)/);
                    if (newParsed && newParsed.length === 7) {
                        parsed = [
                            '', '', '', '', newParsed[4], newParsed[3], newParsed[5], newParsed[6]
                        ]
                    }
                }
                if (parsed && parsed.length === 8) {
                    parsed = parsed.map(string => {
                        if (string) {
                            return string.trim();
                        }
                    });
                    let interprogram_keys = ["анонс","вещани","заставк","ролик","программа передач","погод","эфира","спонсор", "часы"];
                    let program_lower = parsed[4].toLowerCase();
                    if (program_lower.indexOf("реклам") !== -1) {
                        record.is_interprogram = true;
                        record.interprogram_type = 22;
                    } else {
                        interprogram_keys.forEach(interprogram_key => {
                            if (program_lower.indexOf(interprogram_key) !== -1) {
                                this.data.is_interprogram = true;
                            }
                        })
                    }
                    if (this.data.channel.name === "") {
                        this.data.channel.name = parsed[5];
                        if (this.allChannelNames[parsed[5]]) {
                            this.data.channel.id = this.allChannelNames[parsed[5]];
                            await this.loadPrograms();
                        }
                    }
                    if (!this.data.is_interprogram) {
                        if (this.data.program.name === "") {
                            this.data.program.name = parsed[4];
                            this.$nextTick(() => {
                                this.programs.forEach(program => {
                                    if (program.name === parsed[4]) {
                                        this.data.program.id = program.id;
                                        this.data.program.cover_picture = program.cover_picture;
                                    }
                                });
                            })
                        }
                    }
                    if (this.data.is_interprogram) {
                        this.data.short_description = parsed[4];
                    } else {
                        this.data.short_description = parsed[7];
                    }
                    if (this.data.is_interprogram && this.data.channel.id) {
                        if (!this.isRadio) {
                            this.loadInterprogramPackages();
                        }
                    }
                    let date = parsed[6];
                    let year_end;
                    let year;
                    let month;
                    let day;
                    if (date !== "") {
                        date = date.split(";")[0];
                        date = date.replace("–","-");
                        let splitted_min = date.split("-");
                        if (splitted_min.length === 2) {
                            let splitted_min_end = splitted_min[1].split(".");
                            if (splitted_min_end.length === 3) {
                                if (splitted_min_end[2] !== "") {
                                    year_end = splitted_min_end[2];
                                }
                            } else {
                                splitted_min[1] = parseInt(splitted_min[1]);
                                if (splitted_min[1]) {
                                    year_end = splitted_min[1];
                                }
                            }
                            year = parseInt(splitted_min[0]);
                            date = splitted_min[1];
                        }
                         if (date.split(".").length !== 3 && date.split(" ").length !== 3) {
                            let splitted = date.split(" ");
                            if (splitted.length === 1) {
                                year = parseInt(splitted[0]);
                            } else if (splitted.length === 2) {
                                year = parseInt(splitted[1]);
                                let month_names = {"январь": 1, "февраль": 2, "март": 3, "апрель": 4, "май": 5, "июнь": 6, "июль": 7, "август": 8, "сентябрь": 9, "октябрь": 10, "ноябрь": 11, "декабрь": 12};
                                month = splitted[0].toLowerCase();
                                if (month_names[month] !== undefined) {
                                    month = month_names[month];
                                }
                            }
                        } else {
                             if (date.split(".").length !== 3) {
                                 let splitted = date.split(" ");
                                 let month_names = {"января": 1, "февраля": 2, "марта": 3, "апреля": 4, "мая": 5, "июня": 6, "июля": 7, "августа": 8, "сентября": 9, "октября": 10, "ноября": 11, "декабря": 12};
                                 day = splitted[0];
                                 if (month_names[splitted[1]]) {
                                     month = month_names[splitted[1]];
                                 }
                                 year = splitted[2];
                             } else {
                                 date = date.trim();
                                 date = date.replace('/[^0-9.]+/', '');
                                 let splitted = date.split(".");
                                 day = splitted[0];
                                 month = splitted[1];
                                 year = splitted[2];
                             }
                        }
                         this.hideDateInputs = true;
                        if (month) {
                            this.data.date.month = parseInt(month);
                        }
                        if (year) {
                            this.data.date.year = parseInt(year);
                        }
                        if (day) {
                            this.data.date.day = parseInt(day);
                        }
                        this.$nextTick(() => {
                            this.hideDateInputs = false;
                        });
                    }
                }
            },
            loadInterprogramPackages() {
                return new Promise((resolve) => {
                    $.get('/channels/'+this.data.channel.id+'/interprogram-packages').done(res => {
                        this.interprogramPackages = res.data.interprogram_packages;
                        resolve(res.data.interprogramPackages);
                    })
                })
            },
            loadCategories() {
                $.get('/records/categories').done(res => {
                     this.categories = res.data.categories;
                })
            },
            loadChannels() {
                $.get('/channels/ajax').then(res => {
                    this.channelsList = res.data.channels;
                })
            },
            loadPrograms() {
                return new Promise((resolve) => {
                    $.get('/channels/'+this.data.channel.id+'/programs').done(res => {
                        this.programs = res.data.programs;
                        resolve(res.data.programs);
                    })
                })
            },
            selectProgram(program) {
                this.data.program.name = program.name;
                this.data.program.id = program.id;
                this.data.program.cover_picture = program.cover_picture;
            },
            selectChannel(channel) {
                this.data.program.name = '';
                this.data.program.id = null;
                this.data.channel.name = channel.name;
                this.data.channel.id = channel.id;
                this.loadPrograms();
            }
        },
        watch: {
            "data.record.url"() {
                clearTimeout(this.changeUrlTimeout);
                this.changeUrlTimeout = setTimeout(() => {
                    this.getRecordData();
                }, 500)
            }
        },
        computed: {
            otherTypes() {
                let categories = this.categories;
                if (categories.length === 0) {
                    return [];
                }
                categories = categories.filter(category => category.type === 'videos_other').map(category => {
                    return {id: category.id, text: category.name}
                });
                categories.unshift({
                    id: -1,
                    text: '-'
                });
                return categories;
            },
            interprogramTypes() {
                let categories = this.categories;
                if (categories.length === 0) {
                    return [];
                }
                categories = categories.filter(category => category.type === 'interprogram').map(category => {
                    return {id: category.id, text: category.name}
                });
                categories.unshift({
                    id: -1,
                    text: '-'
                });
                return categories;
            },
            advertisingTypes() {
                let categories = this.categories;
                if (categories.length === 0) {
                    return [];
                }
                categories = categories.filter(category => category.type === 'advertising').map(category => {
                    return {id: category.id, text: category.name}
                });
                categories.unshift({
                    id: -1,
                    text: '-'
                });
                return categories;
            },
            isRadio() {
                return this.meta && this.meta.is_radio
            },
            allProgramNames() {
                let names = {};
                this.programs.forEach(program => {
                    names[program.name] = program.id;
                });
                return names;
            },
            allChannelNames() {
               let names = {};
               this.channelsList.forEach(channel => {
                   names[channel.name] = channel.id;
                   if (channel.names) {
                       channel.names.forEach(channelName => {
                           names[channelName.name] = name.channel_id;
                       })
                   }
               });
               return names;
            },
            dayOptions() {
                let year = this.data.date.year;
                let isLeapYear = year > 0 && ((year % 4 === 0) && (year % 100 !== 0)) || (year % 400 === 0);
                let days = [{id: -1, text: 'Неизвестно'}];
                let daysInMonth = [
                    31, isLeapYear ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31
                ];
                let daysInMonthNumber = this.data.date.month > 0 ? daysInMonth[this.data.date.month - 1] : 31;
                for (let i = 1; i <= daysInMonthNumber; i++) {
                    days.push({id: i, text: i.toString()});
                }
                return days;
            },
            monthOptions() {
                let months = [{id: -1, text: 'Неизвестно'}];
                let monthNames = ["Январь","Февраль","Март","Апрель","Май","Июнь","Июль","Август","Сентябрь","Октябрь","Ноябрь","Декабрь"];
                for (let i = 1; i <= 12; i++) {
                    months.push({id: i, text: monthNames[i - 1]});
                }
                return months;
            },
            yearOptions() {
                let years = [{id: -1, text: 'Неизвестно'}];
                for (let i = 1950; i < 2009; i++) {
                    years.push({id: i, text: i.toString()});
                }
                return years;
            },
            filteredPrograms() {
                let programs = [];
                if (this.data.program.name === '') {
                    programs =  this.programs;
                } else {
                    let lowercaseName = this.data.program.name.toLowerCase();
                    programs =  this.programs.filter(program => program.name.toLowerCase().indexOf(lowercaseName) !== -1);
                }
                programs = programs.slice(0, 30);
                return programs;
            },
            filteredChannels() {
                let isRadio = !!this.isRadio;
                if (this.data.channel.name === '') {
                    return this.channelsList.filter(channel => channel.is_federal && channel.is_radio === isRadio);
                } else {
                    let lowercaseName = this.data.channel.name.toLowerCase();
                    return this.channelsList.filter(channel => {
                        if (channel.is_radio !== isRadio) {
                            return false;
                        }
                        if (channel.name.toLowerCase().indexOf(lowercaseName) !== -1) {
                            return true;
                        }
                        if (channel.names) {
                            let names = channel.names.filter(name => name.name.toLowerCase().indexOf(lowercaseName) !== -1);

                            if (names.length > 0) {
                                return true;
                            }
                        }
                        return false;
                    });
                }
            }
        },
        data() {
            return {
                hideDateInputs: false,
                errors: {},
                loading: false,
                response: null,
                interprogramPackages: [],
                isLoadingRecordInfo: false,
                changeUrlTimeout: null,
                data: JSON.parse(JSON.stringify(defaultData)),
                programs: [],
                channelsList: this.channels || [],
                categories: [],
                dataIsSet: false,
                needsUploadRecord: false,
                fileUploadInfo: {
                    url: null,
                    screenshot: null,
                    percent: 0,
                    isUploading: false,
                }
            }
        }
    }
</script>
