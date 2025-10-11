 <template>
    <div class="form record-form">
        <snackbar ref="snackbar"></snackbar>

        <div class="form__preloader" v-show="loading || fileUploadInfo.isUploading">
            <img src="../../../public/img/ajax.gif">
        </div>
        <Response :data="response" v-if="!inModal"/>

        <div class="input-container" v-if="record && canEditAll">
            <label class="input-container__label">Дата добавления</label>
            <div class="input-container__inner">
                <div class="input-container__element-outer">
                    <div class="input-container__overlay-outer">
                        <Datepicker v-model="data.original_added_at"></Datepicker>
                    </div>
                </div>
                <span class="input-container__message">{{errors.original_added_at}}</span>
            </div>
        </div>

        <div class="input-container" v-if="record && canEditAll">
            <label class="input-container__label">Изменить автора на</label>
            <div class="input-container__inner">
                <div class="input-container__element-outer">
                    <div class="input-container__overlay-outer">
                        <select2 theme="default" :customOptions="usersAutocompleteOptions" v-model="data.author_id"></select2>
                    </div>
                </div>
                <span class="input-container__message">{{errors.author_id}}</span>
            </div>
        </div>

         <div class="input-container" v-show="!record || record.source_type !== 'local'">
            <label class="input-container__label" v-if="!isRadio">Ссылка на видео</label>
            <label class="input-container__label" v-else>Ссылка на аудиозапись</label>
            <div class="input-container__inner">
                <div class="input-container__element-outer">
                    <input class="input" v-model="data.record.url" :disabled="data.record.own_code || data.record.use_own_player" />
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



        <div class="input-container" v-if="!params.channel_id" v-show="!data.is_advertising">
            <label class="input-container__label">{{isRadio ? 'Радиостанция' : 'Канал'}}</label>
            <div class="input-container__inner">
                <channel-select
                    v-model="data.channel"
                    :channels-list="channelsList"
                    :disabled="data.is_other"
                    :is-radio="isRadio"
                    @selected="onChannelSelected"
                >
                    <div class="input-container__toggle-buttons">
                        <a class="input-container__toggle-button" :class="{'input-container__toggle-button--active': data.channel.unknown}" @click="setUnknownChannel()">{{isRadio ? "Радиостанция неизвестна" : "Канал неизвестен"}}</a>
                        <a class="input-container__toggle-button" :class="{'input-container__toggle-button--active': data.is_other}" @click="setIsOther()" title="Запись не относится к определенному каналу или содержит сразу несколько фрагментов">Другое</a>
                    </div>
                </channel-select>
                <span class="input-container__message">{{errors.channel}}</span>
            </div>
        </div>
        <div class="input-container" v-if="!(params.is_interprogram || params.program_id | params.is_clip || params.is_advertising)">
            <label class="input-container__label">Программа</label>
            <div class="input-container__inner">
                <div class="input-container__element-outer">
                    <input class="input" v-model="data.program.name" :disabled="(data.is_interprogram && !data.is_program_design) || data.is_clip || data.program.unknown || data.is_advertising  || data.is_other"/>
                    <div class="input-container__toggle-buttons">
                        <a class="input-container__toggle-button" :class="{'input-container__toggle-button--active': data.program.unknown}" @click="setUnknownProgram()">Программа неизвестна</a>
                        <a title="Заставки, анонсы и т.д." class="input-container__toggle-button" :class="{'input-container__toggle-button--active': data.is_interprogram && !data.is_program_design}" @click="setInterprogram()">Межпрограммное пространство</a>
                        <a class="input-container__toggle-button" :class="{'input-container__toggle-button--active': data.is_advertising}" @click="setAdvertising()">Рекламный ролик</a>
                        <a title="Заставки, титры и т.д." class="input-container__toggle-button" :class="{'input-container__toggle-button--active': data.is_program_design}" @click="setIsProgramDesign()">Оформление программы</a>
                        <a class="input-container__toggle-button" :class="{'input-container__toggle-button--active': data.is_clip}" @click="setClip()">Клип</a>
                    </div>
                    <div class="autocomplete__items" v-show="data.is_program_design || (!data.is_interprogram && !data.is_clip && !data.is_advertising && !data.program.unknown)">
                        <a @click="selectProgram(programItem)" class="autocomplete__item" :class="{'autocomplete__item--selected': data.program.id === programItem.id}" v-for="(programItem, $index) in filteredPrograms" :key="$index">
                            <span v-if="programItem.cover_picture" class="autocomplete__item__logo" :style="{backgroundImage: 'url('+programItem.cover_picture.url+')'}"></span>
                            <span class="autocomplete__item__name">{{programItem.name}}</span>
                        </a>
                    </div>
                    <div v-if="data.is_interprogram && !data.is_other && !data.is_advertising" class="record-form__interprogram-packages">
                        <div @click="data.interprogram_package_id = item.id" v-for="(item, $index) in graphics" :key="$index"  class="record-form__interprogram-package" :class="{'record-form__interprogram-package--selected': data.interprogram_package_id === item.id}">
                            <div class="record-form__interprogram-package__cover" :style="{backgroundImage: 'url('+(item.cover)+')'}"></div>
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
        <div class="input-container" v-show="!data.is_other && (data.is_interprogram || params.interprogram_package_id)">
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
                    <date-select
                        v-model="data.date"
                        :hide-day-and-month="data.is_advertising"
                    />
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
        <div class="input-container" v-if="dataIsSet" v-show="!isRadio">
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
<script>
    import Datepicker from 'vuejs-datepicker';
    import * as tus from 'tus-js-client'

    import Response from "./Response.vue";
    import Snackbar from "./Snackbar.vue";
    import ChannelSelect from "./ChannelSelect.vue";
    import DateSelect from "./DateSelect.vue";

    import {interprogramNames} from "@/consts.js";
    import {getYearOptions, parseDate} from "@/modules/dates.js";

    import {getVideoInfo} from "@/modules/video-info.js";

    const usersAutocompleteOptions = {
        ajax: {
            method: 'POST',
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

    const defaultData = {
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
            await this.$nextTick();
            if (this.record) {
                this.data = {
                    original_added_at: new Date(this.record.original_added_at_ts * 1000),
                    author_id: this.record.author_id,
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
                    is_program_design: this.record.program_id && this.record.is_interprogram,
                    record: {
                        title: this.record.title,
                        url: this.record.original_url,
                        id: null,
                        code: this.record.embed_code,
                        covers: [],
                        use_own_player: this.record.use_own_player,
                        source_path: this.record.source_path
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
                if (this.record.channel?.id) {
                    this.loadPrograms();
                    if (this.record.is_interprogram) {
                        if (!this.isRadio) {
                            this.loadGraphicPackages();
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
        components: {Datepicker, DateSelect, ChannelSelect, Snackbar, Response},
        props: {
            uploadEndpoint: {},
            canUpload: {},
            canEditAll: {},
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
                    //let fd = new FormData();
                    //fd.append('record', record);
                    //fd.append('is_radio', this.isRadio ? "1" : "0");
                    this.fileUploadInfo.isUploading = true;


                   this.tusUpload = new tus.Upload(record, {
                        endpoint: this.uploadEndpoint,
                        retryDelays: [0, 1000, 3000, 5000, 5000, 5000, 5000, 5000, 5000, 5000, 5000, 5000],
                        chunkSize: 10 * 1048576,
                        metadata: {
                            filename: record.name,
                        },
                        onError: (error) => {
                            reject();
                            this.$refs.snackbar.show({
                                status: 0,
                                text: 'Ошибка загрузки, попробуйте еще раз или напишите администратору'
                            });
                        },
                        onProgress: (bytesUploaded, bytesTotal) => {
                            this.fileUploadInfo.percent = Math.floor((bytesUploaded / bytesTotal) * 10000) / 100;
                        },
                        onSuccess: async (e) => {
                            let server_upload_id = this.tusUpload.url.split("/");
                            server_upload_id = server_upload_id[server_upload_id.length - 1];
                            $.post('/records/after-upload', {
                                server_upload_id,
                                is_radio: this.isRadio
                            }).done(res => {
                                this.fileUploadInfo.isUploading = false;
                                resolve(res.data);
                                this.fileUploadInfo.url = res.data.url;
                                this.fileUploadInfo.screenshot = res.data.screenshot;
                            }).fail(e => {
                                console.log('fail', e);
                                this.fileUploadInfo.isUploading = false;
                                this.$refs.snackbar.show({
                                    status: 0,
                                    text: e.responseJSON ? (e.responseJSON.message && e.responseJSON.message !== "" ? e.responseJSON.message : e.responseJSON.exception) : "Ошибка загрузки"
                                });
                                reject();
                            });
                        }
                    });
                    this.tusUpload.start();
                })
            },
            onFileInputChange(e) {
                const file = e.target.files[0];
                if (!file) {
                    return;
                }
                this.needsUploadRecord = true;
            },

            async save() {
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
                $.post(this.record ? `/records/${this.record.id}/edit` : '/records/add', data).done(res => {
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
                            this.graphics = [];
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
                this.loadGraphicPackages();
            },
            setInterprogram() {
                this.data.is_interprogram = this.data.is_program_design ? true : !this.data.is_interprogram;
                if (this.data.is_interprogram) {
                    this.data.is_program_design = false;
                    this.data.is_advertising = false;
                    this.data.is_clip = false;
                    this.data.program.unknown = false;
                    if (this.data.channel.id) {
                        if (!this.isRadio) {
                            this.loadGraphicPackages();
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
                this.isLoadingRecordInfo = true;
                const {id, title, code, covers} = await getVideoInfo(this.data.record.url);

                this.isLoadingRecordInfo = false;
                if (!id) {
                    return;
                }

                this.data.record.id = id;
                this.data.record.title = title;
                this.data.record.code = code;
                this.data.record.covers = covers;

                this.parseInfo(title);

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
                    parsed = parsed.map(string => (string ? string.trim() : ''));

                    const program = parsed[4].toLowerCase();
                    if (program.indexOf("реклам") !== -1) {
                        this.data.is_interprogram = true;
                        this.data.interprogram_type = 22;
                    } else {
                        interprogramNames.forEach(name => {
                            if (program.indexOf(name) !== -1) {
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
                            this.loadGraphicPackages();
                        }
                    }
                    const {month, year, day} = parseDate(parsed[6]);

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
            },
            loadGraphicPackages() {
                return new Promise((resolve) => {
                    const url = this.data.is_program_design ? `/programs/${this.data.program.id}/graphics/ajax` : `/channels/${this.data.channel.id}/graphics/ajax`;
                    $.get(url).done(res => {
                        this.graphics = res.data.graphics;
                        resolve(res.data.graphics);
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
                    this.setChannelFullNames();
                })
            },
            loadPrograms() {
                return new Promise((resolve) => {
                    $.get(`/channels/${this.data.channel.id}/programs`).done(res => {
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
            onChannelSelected() {
                this.data.program.name = '';
                this.data.program.id = null;
                this.loadPrograms();
            }
        },
        watch: {
            "data.record.url"() {
                clearTimeout(this.changeUrlTimeout);
                this.changeUrlTimeout = setTimeout(() => {
                    if (this.dataIsSet) {
                        this.getRecordData();
                    }
                }, 500)
            }
        },
        computed: {
            otherTypes() {
                const categories = (this.categories || []).filter(category => category.type === 'videos_other').map(category => {
                    return {id: category.id, text: category.name}
                });
                categories.unshift({
                    id: -1,
                    text: 'Другое'
                });
                return categories;
            },
            interprogramTypes() {
                const categories = (this.categories || []).filter(category => category.type === 'interprogram').map(category => {
                    return {id: category.id, text: category.name}
                });
                categories.unshift({
                    id: -1,
                    text: 'Другое'
                });
                return categories;
            },
            advertisingTypes() {
                const categories = (this.categories || []).filter(category => category.type === 'advertising').map(category => {
                    return {id: category.id, text: category.name}
                });
                categories.unshift({
                    id: -1,
                    text: 'Обычная'
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
                           names[channelName.name] = channelName.channel_id;
                       })
                   }
               });
               return names;
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

        },
        data() {
            return {
                hideDateInputs: false,
                errors: {},
                loading: false,
                response: null,
                graphics: [],
                isLoadingRecordInfo: false,
                changeUrlTimeout: null,
                data: JSON.parse(JSON.stringify(defaultData)),
                yearOptions: getYearOptions(),
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
                },
                usersAutocompleteOptions,
                tusUpload: null
            }
        }
    }
</script>
