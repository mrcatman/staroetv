<template>
    <div class="mass-uploader">
        <div class="form__preloader" v-if="loading">
            <img src="/pictures/ajax.gif">
        </div>
        <snackbar ref="snackbar"></snackbar>
        <div class="input-container" v-if="!records || records.length === 0">
            <label class="input-container__label">Укажите ID группы (ВК)</label>
            <div class="input-container__inner">
                <input class="input" v-model="source" value=""/>
                <span class="input-container__message"></span>
            </div>
        </div>
        <div class="mass-uploader__records">
            <div class="mass-uploader__record" v-for="(record, $index) in visibleRecords" :key="record.player">
                <div class="form__preloader" v-if="record.loading">
                    <img src="/pictures/ajax.gif">
                </div>
                <div class="mass-uploader__record__top">
                    <div class="mass-uploader__record__top__left">
                        <div class="inputs-line">
                            <div class="inputs-line__item" >
                                <div class="inputs-line__item__title">Заголовок</div>
                                <input class="input" v-model="record.record.title" />
                            </div>
                            <div class="mass-uploader__record__buttons">
                                <a class="button" target="_blank" :href="record.player">К видео</a>
                                <a class="button" @click="deleteRecord(record)">Удалить</a>
                            </div>
                        </div>
                        <div class="inputs-line">
                            <div class="inputs-line__item" >
                                <div class="inputs-line__item__title">День</div>
                                <input class="input" v-model="record.date.day" />
                            </div>
                            <div class="inputs-line__item" >
                                <div class="inputs-line__item__title">Месяц</div>
                                <input class="input" v-model="record.date.month" />
                            </div>
                            <div class="inputs-line__item" >
                                <div class="inputs-line__item__title">Год</div>
                                <input class="input" v-model="record.date.year" />
                            </div>
                        </div>
                    </div>
                    <div class="mass-uploader__record__picture" v-if="record.record.cover" :style="{backgroundImage: 'url(' + record.record.cover + ')'}"></div>
                </div>
                <div class="inputs-line">
                    <div class="inputs-line__item" >
                        <div class="inputs-line__item__title">Канал</div>
                        <select2 theme="default" :options="channelOptions" @input ="e => loadPrograms(record)" v-model="record.channel.id"></select2>
                    </div>
                    <div class="inputs-line__item">
                        <div class="mass-uploader__record__toggle-buttons">
                            <a title="Заставки, анонсы и т.д." class="input-container__toggle-button" :class="{'input-container__toggle-button--active': record.is_interprogram}" @click="setInterprogram(record)">Межпрограммное пространство</a>
                            <a class="input-container__toggle-button" :class="{'input-container__toggle-button--active': record.is_advertising}" @click="setAdvertising(record)">Реклама</a>
                            <a title="Заставки, титры и т.д." class="input-container__toggle-button" :class="{'input-container__toggle-button--active': record.is_program_design}" @click="setIsProgramDesign(record)">Оформление программы</a>
                        </div>
                    </div>
                    <div class="inputs-line__item" v-if="(record.channel.id && programsOptions[record.channel.id]) && (!record.is_interprogram || record.is_program_design)">
                        <div class="inputs-line__item__title">Программа</div>
                        <select2 theme="default" :options="programsOptions[record.channel.id]" v-model="record.program.id"></select2>
                        <a class="input-container__toggle-button"@click="loadPrograms(record, true)">Перезагрузить</a>
                    </div>
                    <div class="inputs-line__item" v-if="record.is_interprogram && !record.is_program_design">
                        <div class="inputs-line__item__title">Тип</div>
                        <select2 theme="default" :options="interprogramTypes" v-model="record.interprogram_type"></select2>
                    </div>
                    <div class="inputs-line__item" v-if="record.is_interprogram && !record.is_program_design && record.channel.id && interprogramOptions[record.channel.id]">
                        <div class="inputs-line__item__title">Пакет оформления</div>
                        <select2 theme="default" :options="interprogramOptions[record.channel.id]" v-model="record.interprogram_package_id"></select2>
                        <a  class="input-container__toggle-button"@click="loadInterprogramPackages(record, true)">Перезагрузить</a>
                    </div>
                </div>
                <div class="inputs-line mass-uploader__record__last-line">
                    <div class="inputs-line__item" >
                        <div class="inputs-line__item__title">Короткое описание</div>
                        <input class="input" v-model="record.short_description" />
                    </div>
                </div>
                <div class="inputs-line mass-uploader__record__last-line">
                    <div class="inputs-line__item" >
                        <div class="inputs-line__item__title">Описание</div>
                        <textarea class="input input--textarea" v-model="record.description"></textarea>
                    </div>
                </div>
                <div class="form__bottom">
                    <a @click="save(record)" class="button button--light">Добавить</a>
               </div>
            </div>
        </div>
        <div class="pager-container mass-uploader__pager-container" v-if="parsedRecords.length > 0">
            <b-pagination v-model="currentPage" :total-rows="parsedRecords.length" :per-page="perPage" align="fill" size="sm" class="my-0"></b-pagination>
        </div>

        <div class="box__inner" v-if="parsedRecords.length === 0">
            <a @click="load()" class="button">Загрузить</a>
        </div>
    </div>
</template>
<style lang="scss">
    .mass-uploader {
        &__record {
            background: var(--bg-darker);
            position: relative;
            padding: 1em;
            margin: 0 0 1em;

            .input {
                width: calc(100% - 1em);
                padding: .5em 0;
            }

            .inputs-line {
                padding: 0 0 1.5em;
            }

            &__top {
                display: flex;

                &__left {
                    flex: 1;
                }
            }

            &__picture {
                width: 16em;
                background-size: cover;
                background-position: center;
                margin: 0 0 0 1em;
            }

            &__buttons {
                margin: 2em 1em 0 0;
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
<script>
    import Snackbar from "./Snackbar";
    export default {
        components: {Snackbar},
        data() {
            return {
                source: '',
                records: [],
                programs: {},
                interprogramPackages: {},
                parsedRecords: [],
                channelsList: [],
                currentPage: 1,
                perPage: 30,
                programsLoading: {},
                interprogramPackagesLoading: {},
                loading: false
            }
        },
        mounted() {
            this.loadCategories();
            this.loadChannels();
        },
        computed: {
            interprogramOptions() {
                let options = {};
                Object.keys(this.interprogramPackages).forEach(channelId => {
                    options[channelId] = [
                        {id: -1, text: '-'}
                    ];
                    this.interprogramPackages[channelId].forEach(packageItem => {
                        options[channelId].push({id: packageItem.id, text: packageItem.name || packageItem.years_range})
                    })
                })
                return options;
            },
            programsOptions() {
                let options = {};
                Object.keys(this.programs).forEach(channelId => {
                    options[channelId] = [
                        {id: -1, text: '-'}
                    ];
                    this.programs[channelId].forEach(program => {
                        options[channelId].push({id: program.id, text: program.name})
                    })
                })
                return options;
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
            visibleRecords() {
                console.log((this.currentPage - 1) * this.perPage, this.perPage);
                return this.parsedRecords.slice((this.currentPage - 1) * this.perPage, this.currentPage * this.perPage);
            },
            channelOptions() {
                let data = [];
                this.channelsList.forEach(channel => {
                    data.push({id: channel.id, text: channel.name});
                });
                return data;
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
        },
        methods: {
            save(record) {
                this.$set(record, 'loading', true);

                let data = JSON.parse(JSON.stringify(record));
                data.is_radio = false;
                if (data.channel.id > 0) {

                } else {
                    data.channel.unknown = true;
                }
                if (data.program.id > 0) {

                } else {
                    data.program.unknown = true;
                }

                $.post('/records/add', data).done(res => {
                    this.$set(record, 'loading', false);
                    this.$refs.snackbar.show(res);
                    if (res.status) {
                        this.parsedRecords = this.parsedRecords.filter(recordItem => recordItem.player !== record.player);
                    }
                });

            },
            deleteRecord(record) {
                this.parsedRecords = this.parsedRecords.filter(recordItem => recordItem.player !== record.player);
            },
            parseInfo() {
                this.parsedRecords = this.records.map(recordItem => {

                    let title = recordItem.title;
                    let embedCode = `<iframe frameborder="0" src="${recordItem.player}" allowfullscreen width="100%" height="100%"></iframe>`;
                    let record = {
                        player: recordItem.player,
                        description: recordItem.description,
                        channel: {

                        },
                        program: {

                        },
                        record: {
                            title: recordItem.title,
                            code: embedCode
                        },
                        date: {

                        }
                    };
                    if (recordItem.image.length > 0) {
                        if (recordItem.image[5]) {
                            record.record.cover = recordItem.image[5].url;
                        } else {
                            record.record.cover = recordItem.image[2].url;
                        }
                    }
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
                        let interprogram_keys = ["анонс", "вещани", "заставк", "ролик", "программа передач", "погод", "эфира", "спонсор", "часы"];
                        if (parsed[4]) {
                            let program_lower = parsed[4].toLowerCase();
                            if (program_lower.indexOf("реклам") !== -1) {
                                record.is_interprogram = true;
                                record.interprogram_type = 22;
                            } else {
                                interprogram_keys.forEach(interprogram_key => {
                                    if (program_lower.indexOf(interprogram_key) !== -1) {
                                        record.is_interprogram = true;
                                    }
                                })
                            }
                        }
                        if (this.allChannelNames[parsed[5]]) {
                            record.channel.id = this.allChannelNames[parsed[5]];
                            this.loadPrograms(record);
                        }
                        if (!record.is_interprogram) {
                            //
                        }
                        if (record.is_interprogram) {
                            record.short_description = parsed[4];
                        } else {
                            record.short_description = parsed[7];
                        }
                        if (record.is_interprogram && record.channel.id) {
                            this.loadInterprogramPackages(record);
                        }
                        let date = parsed[6];
                        let year_end;
                        let year;
                        let month;
                        let day;
                        if (date && date !== "") {
                            date = date.split(";")[0];
                            date = date.replace("–", "-");
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
                            if (typeof date === "number") {
                                date = ""+date;
                            }
                            if (date && date.split(".").length !== 3 && date.split(" ").length !== 3) {
                                let splitted = date.split(" ");
                                if (splitted.length === 1) {
                                    year = parseInt(splitted[0]);
                                } else if (splitted.length === 2) {
                                    year = parseInt(splitted[1]);
                                    let month_names = {
                                        "январь": 1,
                                        "февраль": 2,
                                        "март": 3,
                                        "апрель": 4,
                                        "май": 5,
                                        "июнь": 6,
                                        "июль": 7,
                                        "август": 8,
                                        "сентябрь": 9,
                                        "октябрь": 10,
                                        "ноябрь": 11,
                                        "декабрь": 12
                                    };
                                    month = splitted[0].toLowerCase();
                                    if (month_names[month] !== undefined) {
                                        month = month_names[month];
                                    }
                                }
                            } else {
                                if (date && date.split(".").length !== 3) {
                                    let splitted = date.split(" ");
                                    let month_names = {
                                        "января": 1,
                                        "февраля": 2,
                                        "марта": 3,
                                        "апреля": 4,
                                        "мая": 5,
                                        "июня": 6,
                                        "июля": 7,
                                        "августа": 8,
                                        "сентября": 9,
                                        "октября": 10,
                                        "ноября": 11,
                                        "декабря": 12
                                    };
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
                            record.date = {};
                            if (month) {
                                record.date.month = parseInt(month);
                            }
                            if (year) {
                                record.date.year = parseInt(year);
                            }
                            if (day) {
                                record.date.day = parseInt(day);
                            }
                        }
                    }
                    if (!record.channel.id) {
                        let names = Object.keys(this.allChannelNames);
                        let name = names.filter(name => name.length > 0 && title.indexOf(name) !== -1)[0];
                        if (name) {
                            record.channel.id = this.allChannelNames[name];
                        }
                    }
                    return record;
                });
            },
            load() {
                this.loading = true;
                $.post('/mass-upload', {source: this.source}).then(res => {
                    this.loading = false;
                    if (res.status) {
                        this.records = res.data.items;
                        this.parseInfo();
                    } else {
                        this.$refs.snackbar.show(res);
                    }
                })
            },
            loadInterprogramPackages(record, forceReload = false) {
                if (!forceReload && (this.interprogramPackagesLoading[record.channel.id] || this.interprogramPackages[record.channel.id])) {
                    return;
                }
                this.interprogramPackagesLoading[record.channel.id] = true;
                $.get('/channels/'+record.channel.id+'/interprogram-packages').done(res => {
                    this.$set(this.interprogramPackages, record.channel.id, res.data.interprogram_packages);
                    this.interprogramPackagesLoading[record.channel.id] = false;
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
            loadPrograms(record, forceReload = false) {
                if (!forceReload && (this.programsLoading[record.channel.id] || this.programs[record.channel.id])) {
                    return;
                }
                this.programsLoading[record.channel.id] = true;
                $.get('/channels/'+record.channel.id+'/programs').done(res => {
                    this.$set(this.programs, record.channel.id, res.data.programs);
                    this.programsLoading[record.channel.id] = false;
                })
            },
            setAdvertising(record) {
                this.$set(record, 'is_advertising', !record.is_advertising);
                if (record.is_advertising) {
                    record.is_clip = false;
                    record.is_interprogram = false;
                }
            },
            setIsProgramDesign(record) {
                this.$set(record, 'is_program_design', !record.is_program_design);
                this.$set(record, 'is_interprogram', true);
            },
            setInterprogram(record) {
                this.$set(record, 'is_interprogram', !record.is_interprogram);
                if (record.is_interprogram) {
                    record.is_advertising = false;
                    record.is_clip = false;
                    if (record.channel.id) {
                         this.loadInterprogramPackages(record);
                    }
                }
            },
        }
    }
</script>
