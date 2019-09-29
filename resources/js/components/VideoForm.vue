<template>
    <div class="form video-form">
        <div class="form__preloader" v-show="loading"></div>
        <Response :data="response"/>
        <div class="input-container">
            <label class="input-container__label">Ссылка на видео</label>
            <div class="input-container__inner">
                <div class="input-container__element-outer">
                    <div class="input-container__preloader" v-show="isLoadingVideoInfo">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                    <input class="input" v-model="data.video.url"/>
                    <div class="input-container__description">ВК либо Youtube</div>
                    <div class="video-form__player-container__outer" v-show="data.video.code || data.video.covers.length > 0 || data.program.cover_picture">
                        <div class="video-form__player-container" v-html="data.video.code"></div>
                        <div class="video-form__covers">
                            <img class="video-form__cover" v-for="(cover, $index) in data.video.covers" :class="{'video-form__cover--active': cover === data.cover}" @click="data.cover = cover" :src="cover" />
                            <img class="video-form__cover" v-if="data.program.cover_picture" :class="{'video-form__cover--active': data.program.cover_picture.url === data.cover}" @click="data.cover = data.program.cover_picture.url" :src="data.program.cover_picture.url" />
                        </div>
                    </div>
                </div>
                <span class="input-container__message">{{errors.url}}</span>
            </div>
        </div>
        <div class="input-container">
            <label class="input-container__label">Канал</label>
            <div class="input-container__inner">
                <div class="input-container__element-outer">
                    <div class="input-container__overlay-outer">
                        <div class="input-container__disabled-overlay" v-if="data.channel.unknown"></div>
                        <input class="input" v-model="data.channel.name"/>
                    </div>
                    <div class="input-container__toggle-buttons">
                        <a class="input-container__toggle-button" :class="{'input-container__toggle-button--active': data.channel.unknown}" @click="setUnknownChannel()">Канал неизвестен</a>
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
        <div class="input-container">
            <label class="input-container__label">Программа</label>
            <div class="input-container__inner">
                <div class="input-container__element-outer">
                    <div class="input-container__overlay-outer">
                        <div class="input-container__disabled-overlay" v-if="data.is_interprogram || data.program.unknown"></div>
                        <input class="input" v-model="data.program.name"/>
                    </div>
                    <div class="input-container__toggle-buttons">
                        <a class="input-container__toggle-button" :class="{'input-container__toggle-button--active': data.program.unknown}" @click="setUnknownProgram()">Программа неизвестна</a>
                        <a class="input-container__toggle-button" :class="{'input-container__toggle-button--active': data.is_interprogram}" @click="setInterprogram()">Межпрограммное пространство</a>
                    </div>
                    <div class="autocomplete__items" v-show="!data.is_interprogram && !data.program.unknown">
                        <a @click="selectProgram(programItem)" class="autocomplete__item" :class="{'autocomplete__item--selected': data.program.id === programItem.id}" v-for="(programItem, $index) in filteredPrograms" :key="$index">
                            <span v-if="programItem.cover_picture" class="autocomplete__item__logo" :style="{backgroundImage: 'url('+programItem.cover_picture.url+')'}"></span>
                            <span class="autocomplete__item__name">{{programItem.name}}</span>
                        </a>
                    </div>
                    <div v-if="data.is_interprogram" class="video-form__interprogram-packages">
                        <div @click="data.interprogram_package_id = item.id" v-for="(item, $index) in interprogramPackages" :key="$index"  class="video-form__interprogram-package" :class="{'video-form__interprogram-package--selected': data.interprogram_package_id === item.id}">
                            <div class="video-form__interprogram-package__cover" :style="{backgroundImage: 'url('+item.cover_picture+')'}"></div>
                            <div class="video-form__interprogram-package__name">{{item.name}}</div>
                        </div>
                        <div class="video-form__interprogram-package" @click="data.interprogram_package_id = null"  :class="{'video-form__interprogram-package--selected': data.interprogram_package_id === null}">
                            <div class="video-form__interprogram-package__cover" style="background-image: url('/pictures/unknown.png')"></div>
                            <div class="video-form__interprogram-package__name">Другое</div>
                        </div>
                    </div>
                </div>
                <span class="input-container__message">{{errors.program}}</span>
            </div>
        </div>
        <div class="input-container">
            <label class="input-container__label">Дата выхода</label>
            <div class="input-container__inner">
                <div class="input-container__element-outer">
                    <div class="inputs-line">
                        <div class="inputs-line__item">
                            <div class="inputs-line__item__title">День</div>
                            <select2 theme="default" :options="dayOptions" v-model="data.date.day"></select2>
                        </div>
                        <div class="inputs-line__item">
                            <div class="inputs-line__item__title">Месяц</div>
                            <select2 theme="default" :options="monthOptions" v-model="data.date.month"></select2>
                        </div>
                        <div class="inputs-line__item">
                            <div class="inputs-line__item__title">Год</div>
                            <select2 theme="default" :options="yearOptions" v-model="data.date.year"></select2>
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

        <a class="button" @click="save()">Сохранить</a>
    </div>
</template>
<style lang="scss">
    .video-form {
        &__covers {
            display: flex;
            flex-wrap: wrap;
            margin: 0 0 0 1em;
        }

        &__cover {
            height: 7em;
            margin: 0 .5em;
            border: 2px solid rgba(255, 255, 255, 0);
            cursor: pointer;
            &--active {
                box-shadow: 0 0 0.5em #000;
            }
            &:hover {
                border: 2px dashed #555;
            }
        }
        &__player-container {
            &__outer {
                display: flex;
                padding: 1em;
                background: #eee;
                margin: .5em 0 0;
            }
        }
        &__interprogram-packages {
            background: #eee;
            padding: .5em;
            margin: 1em 0 0;
            color: #333;
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
    let defaultData = {
        is_interprogram: false,
        interprogram_package_id: null,
        cover: '',
        video: {
            url: '',
            id: null,
            code: null,
            covers: []
        },
        short_description: '',
        date: {
            year: -1,
            month: -1,
            day: -1
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
        mounted() {
            if (this.video) {
                this.data = {
                    is_interprogram: this.video.is_interprogram,
                    interprogram_package_id:  this.video.interprogram_package_id,
                    cover: this.video.cover,
                    video: {
                        url: this.video.original_url,
                        id: null,
                        code: this.video.embed_code,
                        covers: []
                    },
                    short_description:  this.video.short_description,
                    date: {
                        year: this.video.year,
                        month: this.video.month,
                        day: this.video.day
                    },
                    program: {
                        name: this.video.program ? this.video.program.name : '',
                        id: this.video.program ? this.video.program.id : null,
                        cover_picture: this.video.program ? this.video.program.cover_picture : null,
                        unknown: !(this.video.program_id > 0) && !this.video.is_interprogram,
                    },
                    channel: {
                        name: this.video.channel ? this.video.channel.name : '',
                        id: this.video.channel ? this.video.channel.id : null,
                        unknown: !(this.video.channel_id > 0),
                    }
                };
                if (this.video.channel && this.video.channel.id) {
                    this.loadPrograms();
                }
            }
        },
        components: {Response},
        props: ['channels', 'video'],
        methods: {
            save() {
                this.loading = true;
                $.post(this.video ? '/videos/' + this.video.id : '/videos/add', this.data).done(res => {
                    this.loading = false;
                    this.response = res;
                    this.errors = res.errors || {};
                    window.scrollTo(0, 0);
                    if (res.status) {
                        this.response.text+= `<a target=_blank href='/videos/${res.data.video.id}'>Перейти к видео</a>`;
                        this.data = JSON.parse(JSON.stringify(defaultData));
                        this.programs = [];
                        this.interprogramPackages = [];
                    }
                });
            },
            setInterprogram() {
                this.data.is_interprogram = !this.data.is_interprogram;
                if (this.data.is_interprogram) {
                    this.data.program.unknown = false;
                    if (this.data.channel.id) {
                        this.loadInterprogramPackages();
                    }
                }
            },
            setUnknownProgram() {
                this.data.program.unknown = !this.data.program.unknown;
                if (this.data.program.unknown) {
                    this.data.is_interprogram = false;
                }
            },
            setUnknownChannel() {
                this.data.channel.unknown = !this.data.channel.unknown;
            },
            async getVideoData() {
                let url = this.data.video.url;
                let youtubeData = url.match(/^.*((m\.)?youtu\.be\/|vi?\/|u\/\w\/|embed\/|\?vi?=|\&vi?=)([^#\&\?]*).*/);
                if (youtubeData && youtubeData.length === 4) {
                    let id = youtubeData[3];
                    let code = `<iframe width="560" height="315" src="https://www.youtube.com/embed/${id}" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>`;
                    this.data.video.id = id;
                    this.data.video.code = code;
                    this.data.video.covers = [];
                    ['0', '1', '2', '3', 'hqdefault'].forEach(frame => {
                        this.data.video.covers.push(`https://img.youtube.com/vi/${id}/${frame}.jpg`);
                    });
                    this.isLoadingVideoInfo = true;
                    $.post('/videos/getinfo', {youtube_video_id: id}).done(async res => {
                        this.isLoadingVideoInfo = false;
                        if (res.status && res.data.youtube_response && res.data.youtube_response.items && res.data.youtube_response.items.length > 0) {
                            let video = res.data.youtube_response.items[0].snippet;
                            let title = video.title;
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
                        id = vkData[2] + '_' + vkData[3];
                    }
                    if (id) {
                        this.isLoadingVideoInfo = true;
                        $.post('/videos/getinfo', {vk_video_id: id}).done(async res => {
                            this.isLoadingVideoInfo = false;
                            if (res.status && res.data.vk_response && res.data.vk_response.response && res.data.vk_response.response.items.length > 0) {
                                let video = res.data.vk_response.response.items[0];
                                this.data.video.id = id;
                                this.data.video.code = `<iframe width="560" height="315" src=${video.player} frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>`;
                                this.data.video.covers = [video.image[video.image.length - 1].url];
                                let title = video.title;
                                this.parseInfo(title);
                            }
                        })
                    }
                }
            },
            async parseInfo(title) {
                let parsed = title.match(/((.*?){0,1}staroetv.su(.*?){0,1})?[\])\\/ ]{0,2}(.*?)\((.*?), (.*?)\)(.*)/);
                if (parsed && parsed.length === 8) {
                    parsed = parsed.map(string => {
                        if (string) {
                            return string.trim();
                        }
                    });
                    console.log(parsed);
                    let interprogram_keys = ["анонс","вещани","реклам","заставк","ролик","программа передач","погод","эфира","спонсор", "часы"];
                    let program_lower = parsed[4].toLowerCase();
                    interprogram_keys.forEach(interprogram_key => {
                        if (program_lower.indexOf(interprogram_key) !== -1) {
                            this.data.is_interprogram = true;
                        }
                    })
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
                        this.loadInterprogramPackages();
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
                        if (month) {
                            this.data.date.month = parseInt(month);
                        }
                        if (year) {
                            this.data.date.year = parseInt(year);
                        }
                        if (day) {
                            this.$nextTick(() => {
                                this.data.date.day = parseInt(day);
                            })
                        }
                        console.log(date, day, month, year, year_end);
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
            "data.video.url"() {
                clearTimeout(this.changeUrlTimeout);
                this.changeUrlTimeout = setTimeout(() => {
                    this.getVideoData();
                }, 500)
            }
        },
        computed: {
            allProgramNames() {
                let names = {};
                this.programs.forEach(program => {
                    names[program.name] = program.id;
                });
                return names;
            },
            allChannelNames() {
               let names = {};
               this.channels.forEach(channel => {
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
                if (this.data.program.name === '') {
                    return this.programs;
                } else {
                    let lowercaseName = this.data.program.name.toLowerCase();
                    return this.programs.filter(program => program.name.toLowerCase().indexOf(lowercaseName) !== -1);
                }
            },
            filteredChannels() {
                if (this.data.channel.name === '') {
                    return this.channels.filter(channel => channel.is_federal);
                } else {
                    let lowercaseName = this.data.channel.name.toLowerCase();
                    return this.channels.filter(channel => channel.name.toLowerCase().indexOf(lowercaseName) !== -1);
                }
            }
        },
        data() {
            return {
                errors: {

                },
                loading: false,
                response: null,
                interprogramPackages: [],
                isLoadingVideoInfo: false,
                changeUrlTimeout: null,
                data: JSON.parse(JSON.stringify(defaultData)),
                programs: [],

            }
        }
    }
</script>