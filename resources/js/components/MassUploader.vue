<template>
    <div class="mass-uploader">
        <div class="form__preloader" v-if="loading">
            <img src="/pictures/ajax.gif">
        </div>
        <snackbar ref="snackbar"></snackbar>
        <div class="input-container" v-if="!records || records.length === 0">
            <label class="input-container__label">Укажите ID группы (ВК) или никнейм канала (Youtube)</label>
            <div class="input-container__inner">
                <input class="input" v-model="source" value=""/>
                <span class="input-container__message"></span>
            </div>

        </div>
        <div class="input-container" v-if="!records || records.length === 0">
            <label class="input-container__label">Токен доступа к странице результатов Youtube (если есть)</label>
            <div class="input-container__inner">
                <input class="input" v-model="nextPageToken"/>
                <span class="input-container__message"></span>
            </div>

        </div>
        <div class="mass-uploader__records">
            <div class="mass-uploader__record" :class="{'mass-uploader__record--collapsed': record.collapsed}" v-for="(record, $index) in visibleRecords" :key="record.player">
                <div class="form__preloader" v-if="record.loading">
                    <img src="/pictures/ajax.gif">
                </div>
                <a class="mass-uploader__record__collapse" @click="record.collapsed = !record.collapsed">{{!record.collapsed ? 'Скрыть' : 'Показать'}}</a>
                <div class="mass-uploader__record__content"  v-if="record.collapsed">
                    <div class="inputs-line">
                        <div class="inputs-line__item" >
                            <div class="inputs-line__item__title">Заголовок</div>
                            <input class="input" v-model="record.record.title" />
                        </div>
                        <div class="mass-uploader__record__buttons">
                           <a class="button" @click="deleteRecord(record)">Удалить</a>
                        </div>
                    </div>
                </div>
                <div class="mass-uploader__record__content" v-else>
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
                            <select2 theme="default" :options="channelOptions" @input ="e => findProgram(record)" v-model="record.channel.id"></select2>
                            <a class="input-container__toggle-button"@click="loadChannels()">Перезагрузить</a>
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
                            <a class="input-container__toggle-button"@click="reloadPrograms(record)">Перезагрузить</a>
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
                            <div class="inputs-line__item__title">Файл на сервере</div>
                            <select2 theme="default" :options="storageFiles" v-model="record.storage_file"></select2>
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
        </div>
        <a class="button" @click="loadMore()" v-if="parsedRecords.length > 0 && nextPageToken && nextPageToken !== ''">Загрузить еще</a>
        <div class="mass-uploader__next-token" v-if="parsedRecords.length > 0 && nextPageToken && nextPageToken !== ''">Токен доступа к следующей странице: <strong>{{nextPageToken}}</strong></div>
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
        &__next-token {
            margin: 1em 0;
        }
        &__record {
            background: var(--bg-darker);
            position: relative;
            padding: 1em;
            margin: 0 0 1em;
            &__collapse {
                display: inline-block;
                margin-bottom: .5em;
                cursor: pointer;
                border-bottom: 1px dashed;
                color: #888;
            }
            .input {
                width: calc(100% - 1em);
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
const monthNames = {
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
    import Snackbar from "./Snackbar";
    export default {
        components: {Snackbar},
        data() {
            return {
                source: '',
                records: [],
                files: [],
                programs: {},
                interprogramPackages: {},
                parsedRecords: [],
                channelsList: [],
                currentPage: 1,
                perPage: 30,
                programsLoading: {},
                interprogramPackagesLoading: {},
                loading: false,
                nextPageToken: ''
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
            storageFiles() {
                const files = [
                    {id: '', text: '...'},
                ]
                this.files.forEach(file => {
                    files.push({
                        id: file,
                        text: file
                    })
                });
                return files;
            },
            interprogramTypes() {
                let categories = this.categories;
                if (!categories || categories.length === 0) {
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
                if (!categories || categories.length === 0) {
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
                //console.log((this.currentPage - 1) * this.perPage, this.perPage);
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
                    let name = channel.name;
                    if (channel.city) {
                        name = `${name} (${channel.city})`;
                    }
                    names[name] = channel.id;
                    if (channel.names) {
                        channel.names.forEach(additionalNameData => {
                            names[additionalNameData.name] = channel.id;
                        })
                    }
                });
                return names;
            },
        },
        methods: {
            async reloadPrograms(record) {
                await this.loadPrograms(record, true)
                await this.findProgram(record);
            },
            async findProgram(record) {
                console.log(record.channel.id);
                if (!record.channel.id) {
                    return;
                }
                await this.loadPrograms(record);
                let programName = record._parsed[4];
                if (!programName) return;
                if (programName.indexOf('Д/с') !== -1 || programName.indexOf('Д/ф') !== -1 || programName.indexOf('Д.ф') !== -1 || programName.indexOf('Д.с') !== -1 || programName.indexOf('Д. ф') !== -1 || programName.indexOf('Д. с') !== -1) {
                    programName = 'Документальные фильмы';
                }
                if (programName.indexOf('Концерт') !== -1 || programName.indexOf('концерт') !== -1) {
                    programName = 'Концерты';
                }
                let program = this.programs[record.channel.id] ? this.programs[record.channel.id].filter(program => program.name === programName)[0] : null;
                if (!program) {
                    program = this.programs[record.channel.id] ? this.programs[record.channel.id].filter(program => program.name.indexOf(programName) !== -1)[0] : null;
                }
                if (program) {
                    record.program.id = program.id;
                } else {
                    console.log(`Program not found: ${programName} (channel id: ${record.channel.id}, count: ${this.programs[record.channel.id] ? this.programs[record.channel.id].length : '-'})`);
                }
            },
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
            async parseInfo() {
                for (let recordItem of this.records) {
                    if (!recordItem._ready) {
                        recordItem._ready = true;
                        let title = recordItem.title;
                        let embedCode = `<iframe frameborder="0" src="${recordItem.player}" allowfullscreen width="100%" height="100%"></iframe>`;
                        let record = {
                            _parsed: [],
                            player: recordItem.player,
                            description: recordItem.description,
                            channel: {},
                            program: {},
                            record: {
                                title: recordItem.title,
                                code: embedCode
                            },
                            date: {}
                        };
                        if (recordItem.image.length > 0) {
                            if (recordItem.image[5]) {
                                record.record.cover = recordItem.image[5].url;
                            } else {
                                if (recordItem.image[2]) {
                                    record.record.cover = recordItem.image[2].url;
                                } else {
                                    record.record.cover = recordItem.image[0].url;
                                }
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
                            let interprogram_keys = ["анонс", "вещани", "заставк", "ролик", "программа передач", "эфира", "спонсор", "часы"];
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
                            record._parsed = parsed;
                            if (this.allChannelNames[parsed[5]]) {
                                record.channel.id = this.allChannelNames[parsed[5]];
                            } else {
                                console.log(`Channel not found: ${parsed[5]} (count: ${Object.keys(this.allChannelNames).length})`);
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
                                date = date.replace("~", "");
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
                                    date = "" + date;
                                }
                                if (date && date.split(".").length !== 3 && date.split(" ").length !== 3) {
                                    let splitted = date.split(" ");
                                    if (splitted.length === 1) {
                                        year = parseInt(splitted[0]);
                                    } else if (splitted.length === 2) {
                                        year = parseInt(splitted[1]);
                                        month = splitted[0].toLowerCase();
                                        if (monthNames[month]) {
                                            month = monthNames[month];
                                        }
                                    }
                                } else {
                                    if (date && date.split(".").length !== 3) {
                                        let splitted = date.split(" ");

                                        day = splitted[0];
                                        if (monthNames[splitted[1]]) {
                                            month = monthNames[splitted[1]];
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
                        const file = this.files.map(file => file.replace('.mp4', '')).filter(file => file === record.record.title)[0];
                        if (file) {
                            record.storage_file = `${file}.mp4`;
                        }
                        if (!record.channel.id) {
                            let names = Object.keys(this.allChannelNames);
                            let name = names.filter(name => name.length > 0 && title.indexOf(name) !== -1)[0];
                            if (name) {
                                record.channel.id = this.allChannelNames[name];
                            }
                        }
                        record.collapsed = record.date.year && record.date.year >= 2011;
                        this.parsedRecords.push(record);
                    }
                    for (let record of this.parsedRecords) {
                        await this.findProgram(record);
                    }
                }
            },
            loadMore() {
                this.loading = true;
                $.post('/mass-upload', {source: this.source, next_page_token: this.nextPageToken}).then(res => {
                    this.loading = false;
                    if (res.status) {
                        if (res.data.next_page_token) {
                            this.nextPageToken = res.data.next_page_token;
                        }
                        this.records = [...this.records, ...res.data.items];
                        this.files = res.data.files;
                        this.parseInfo();
                    } else {
                        this.$refs.snackbar.show(res);
                    }
                })
            },
            load() {
                this.loading = true;
                let data = {source: this.source};
                if (this.nextPageToken !== '') {
                    data.next_page_token = this.nextPageToken;
                }
                $.post('/mass-upload', data).then(res => {
                    this.loading = false;
                    if (res.status) {
                        if (res.data.next_page_token) {
                            this.nextPageToken = res.data.next_page_token;
                        }
                        this.records = res.data.items;
                        this.files = res.data.files;
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
                $.get('/channels/'+record.channel.id+'/graphics/ajax').done(res => {
                    this.$set(this.interprogramPackages, record.channel.id, res.data.graphics);
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
                return new Promise(resolve => {
                    if (!forceReload && (this.programsLoading[record.channel.id] || this.programs[record.channel.id])) {
                        resolve();
                        return;
                    }
                    this.programsLoading[record.channel.id] = true;
                    $.get('/channels/'+record.channel.id+'/programs').done(res => {
                        this.$set(this.programs, record.channel.id, res.data.programs);
                        this.programsLoading[record.channel.id] = false;
                        resolve();
                    })
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
