<template>
    <form @submit="onSubmit" ref="form" method="GET" :action="action" class="records-search--container">
        <div class="records-search">
            <div class="records-search__inner">
                <div class="records-search__title">Поиск по записям</div>
                <div class="records-search__input-container">
                    <input v-model="data.search" class="input" placeholder="" name="search"/>
                </div>
                <button class="button button--light">Найти</button>
                <a class="records-search__expand" @click="extend()">
                    <span class="records-search__expand__inner">
                        {{showExtended ? "Свернуть расширенный поиск" : "Расширенный поиск"}}
                    </span>
                </a>
            </div>
            <div class="records-search__extended" v-show="showExtended" v-if="loaded">
                <div class="records-search__extended__row">
                    <div class="inputs-line">
                        <div class="inputs-line__item">
                            <div class="inputs-line__item__title">{{isRadio ? "Радиостанции" : "Каналы"}}</div>
                            <div class="input-container__overlay-outer">
                                <div class="input-container__disabled-overlay" v-if="isAdvertising"></div>
                                <div class="input-container__loading-text" v-if="channels.loading">Загрузка...</div>
                                <input type="hidden" name="channels" :value="selectedChannelsIds" />
                                <select2 :customOptions="channels.selectOptions" v-if="!channels.loading" multiple :options="channels.list" v-model="channels.selected"></select2>
                            </div>
                            <div class="input-container__toggle-buttons">
                                <a class="input-container__toggle-button" :class="{'input-container__toggle-button--active': isAdvertising}" @click="isAdvertising = !isAdvertising">Реклама</a>
                            </div>
                        </div>
                        <div class="inputs-line__item" v-if="channels.selected.length > 0">
                            <div class="inputs-line__item__title">Программа</div>
                            <div class="input-container__overlay-outer">
                                <div class="input-container__disabled-overlay" v-if="isInterprogram || isAdvertising"></div>
                                <div class="input-container__loading-text" v-if="programs.loading">Загрузка...</div>
                                <input type="hidden" name="programs" :value="selectedProgramsIds" />
                                <select2 v-if="!programs.loading" multiple :options="programs.list" v-model="programs.selected"></select2>
                            </div>
                            <div class="input-container__toggle-buttons">
                                <a title="Заставки, анонсы и т.д." class="input-container__toggle-button" :class="{'input-container__toggle-button--active': isInterprogram}" @click="isInterprogram = !isInterprogram">Межпрограммное пространство</a>
                            </div>
                        </div>
                        <div class="records-search__dates">
                            <div class="inputs-line" v-if="!datesRange">
                                <div class="inputs-line__item">
                                    <div class="inputs-line__item__title">Год</div>
                                    <select2 theme="default" :options="yearOptions" v-model="dates.year"></select2>
                                </div>
                                <div class="inputs-line__item">
                                    <div class="inputs-line__item__title">Месяц</div>
                                    <select2 theme="default" :options="monthOptions" v-model="dates.month"></select2>
                                </div>
                                <div class="inputs-line__item">
                                    <div class="inputs-line__item__title">День</div>
                                    <select2 theme="default" :options="dayOptions" v-model="dates.day"></select2>
                                </div>
                            </div>
                            <div class="inputs-line" v-else>
                                <div class="inputs-line__item">
                                    <div class="inputs-line__item__title">От</div>
                                    <Datepicker :disabledDates="disabledDates" v-model="range.start"/>
                                </div>
                                <div class="inputs-line__item">
                                    <div class="inputs-line__item__title">И до</div>
                                    <Datepicker :disabledDates="disabledDates" v-model="range.end"/>
                                </div>
                            </div>
                            <div class="input-container__toggle-buttons">
                                <a class="input-container__toggle-button" :class="{'input-container__toggle-button--active': datesRange}" @click="datesRange = !datesRange">Выбрать диапазон дат</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="records-search__sort">
                    <span class="records-search__sort__title">Сортировать по: </span>
                    <a class="records-search__sort__option" :class="{'records-search__sort__option--active': data.sort === option.key}" @click="setSort(option)" :key="$index" v-for="(option, $index) in sortOptions">
                        <span class="records-search__sort__option__title">{{option.title}}</span>
                        <span class="records-search__sort__option__arrow" v-if="data.sort === option.key">{{data.sort_order === 'asc' ? '↑' : '↓'}}</span>
                    </a>
                </div>
            </div>
        </div>
        <div class="records-search__result" v-if="showResults">
            <div class="form__preloader" v-show="isLoading"><img src="/pictures/ajax.gif"></div>
            <div class="row">
                <div class="box box--dark">
                    <div class="box__inner">
                        <div class="records-list__pager-container records-list__pager-container--top" v-show="resultsList.last_page > 1">
                            <pagination :limit="3" :data="resultsList" @pagination-change-page="getResults"></pagination>
                        </div>
                        <div class="records-search__nothing-found" v-if=" resultsList.data.length === 0">
                            По вашему запросу ничего не найдено
                        </div>
                        <div v-if="isRadio" class="records-list">
                            <a :href="record.url"  v-for="(record) in resultsList.data" :key="record.id" class="radio-recording">
                                <div class="radio-recording__button">
                                    <i class="fa fa-play"></i>
                                </div>
                                <div class="radio-recording__texts">
                                    <span v-html="getHighlights(record.title)" class="radio-recording__title"></span>
                                    <div class="radio-recording__timecodes">
                                        <div class="radio-recording__timecodes__line" v-for="(line, $index) in getDescriptionHighlights(record.description)" :key="$index">
                                            <i class="fa fa-play"></i>
                                            <span class="radio-recording__timecodes__line__text" v-html="getHighlights(line, true)"></span>
                                        </div>
                                    </div>
                                    <!--
                                    <div class="radio-recording__info">
                                        <span class="radio-recording__date">
                                            <i class="fa fa-calendar"></i>{{record.created_at}}
                                        </span>
                                        <span class="radio-recording__listens">
                                            <i class="fa fa-headphones-alt"></i>{{record.views}}
                                        </span>
                                    </div>
                                    -->
                                </div>
                            </a>
                        </div>
                        <div v-else class="records-list">
                            <a :href="record.url" v-for="(record) in resultsList.data" :key="record.id" class="record-item">
                                <div class="record-item__cover" :style="{backgroundImage: `url(${record.cover})`}"></div>
                                <div class="record-item__texts">
                                    <span v-html="getHighlights(record.title)" class="record-item__title"></span>
                                    <div class="record-item__timecodes">
                                        <div class="record-item__timecodes__line" v-for="(line, $index) in getDescriptionHighlights(record.description)" :key="$index">
                                            <i class="fa fa-play"></i>
                                            <span class="record-item__timecodes__line__text" v-html="getHighlights(line, true)"></span>
                                        </div>
                                    </div>
                                   <!-- <div class="record-item__info">
                                        <span class="record-item__date"><i class="fa fa-calendar"></i>{{record.created_at}}</span>
                                        <span class="record-item__views"><i class="fa fa-eye"></i>{{record.views}}</span>
                                    </div> -->
                                </div>
                            </a>
                        </div>
                        <div class="records-list__pager-container" v-show="resultsList.last_page > 1">
                            <pagination :limit="3" :data="resultsList" @pagination-change-page="getResults"></pagination>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</template>
<style lang="scss">
    @import "../../sass/_variables";
    .records-search {
        box-shadow: var(--block-box-shadow);
        border-bottom: 1px solid var(--border-color);
        background: var(--box-color);
        &__inner {
            padding: 1.75em;
            display: flex;
            align-items: center;
            background: var(--bg-darker);
            @include mobile {
                padding: 1em 1em .5em .5em;
                justify-content: flex-end;
                font-size: .75em;
                flex-wrap: wrap;
            }
        }

        &__form {
            display: flex;
            align-items: center;
            flex: 1;
            margin: 0 0 0 1em;
        }

        &__title {
            font-size: 1.25em;
            font-weight: 500;
            @include mobile {
                text-align: left;
                margin: 0 0 0 .75em;
                width: 100%;
            }
        }

        &__input-container {
            flex: 1;
            margin: 0 1.75em 0 1em;
            .input {
                flex: 1;
                width: 100%;
            }
        }

        &__expand {
            color: var(--text-lighter);
            cursor: pointer;
            border-bottom: 1px dashed var(--text-lighter);
            margin: 0 0 0 1em;
            @include mobile {
                text-align: left;
                margin: 1em 0 1em 1em;
                width: 100%;
                font-size: 1.125em;
                border-bottom: none;
            }
            &__inner {
                @include mobile {
                    border-bottom: 1px dashed;
                }
            }
            &:hover {
                color: #777;
            }
        }

        .select2 {
            display: block;
            width: 100%!important;
        }

        &__extended {
            border-top: 1px solid var(--border-color);
            &__row {
                flex: 1;
                padding: 1em 1.5em;
                display: flex;
                .inputs-line {
                    flex: 1;
                }
            }

            &__input {
                width: calc(33% - 1em);
                margin: 0 1em 0 0;
            }
        }
        &__dates {
            flex: 1;
        }
        &__nothing-found {
            font-size: 1.25em;
            font-weight: bold;
        }
        &__sort {
            font-size: 1.125em;
            background: var(--bg-darker);
            border-top: 1px solid var(--border-color);
            padding: 1em 1.25em;

            &__title {
                margin: 0 .5em 0 0;
                font-weight: bold;
            }

            &__option {
                color: var(--text-lighter);

                &--active {
                    border-bottom: 1px dashed;
                }
                &__title {
                    margin: 0 .5em 0 0;
                    cursor: pointer;
                }
            }
        }
        &__channel {
            display: flex;
            align-items: center;

            &__logo {
                width: 3em;
                height: auto;
                margin: 0 1em 0 0;
            }

            &__main-name {
                font-weight: bold;
            }

            &__additional-names {
                color: #999;
                font-size: .75em;
            }
        }
        &__result {
            .record-item {
                font-size: 1.125em;
            }
        }
    }

    .select2-results__option--highlighted .records-search__channel__additional-names {
        color: #fff;
    }
</style>
<script>
    import $ from 'jquery';
    import Datepicker from 'vuejs-datepicker';

    const selectOptions = {
        templateResult: (channel) => {
            let additionalNames = '';
            if (channel.names && channel.names.length > 0) {
                let names = channel.names.map(name => name.name).filter(name => name.length > 0);
                names = [...new Set(names)];
                additionalNames = names.join(", ");
            }
            let html = `<div class="records-search__channel">
                ${channel.logo ? `<img alt="${channel.name}" class="records-search__channel__logo" src="${channel.logo.url}"/>` : ''}
                <div class="records-search__channel__names">
                    <div class="records-search__channel__main-name">${channel.name}</div>
                    ${additionalNames.length > 0 ? `<div class="records-search__channel__additional-names">${additionalNames}</div>` : ''}
                </div>
            </div>`;
            return $(html);
        },
        templateSelection: (channel) => {
            return channel.name;
        },
        matcher: (termData, channel) => {
            let term = termData.term;
            if (!term || term.length === 0) {
                return channel;
            }
            term = term.toLocaleLowerCase();
            if (channel.name.toLocaleLowerCase().indexOf(term) !== -1) {
                return channel;
            }
            if (channel.names) {
                for (let index in channel.names) {
                    let name = channel.names[index].name.toLocaleLowerCase();
                    if (name.indexOf(term) !== -1) {
                        return channel;
                    }
                }
            }
            return null;
        }
    };

    export default {
        components: {
            Datepicker
        },
        computed: {
            selectedProgramsIds() {
                return this.programs.selected.join(",");
            },
            selectedChannelsIds() {
                return this.channels.selected.join(",");
            },
            dayOptions() {
                let year = this.dates.year;
                let isLeapYear = year > 0 && ((year % 4 === 0) && (year % 100 !== 0)) || (year % 400 === 0);
                let days = [{id: -1, text: 'Неизвестно'}];
                let daysInMonth = [
                    31, isLeapYear ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31
                ];
                let daysInMonthNumber = this.dates.month > 0 ? daysInMonth[this.dates.month - 1] : 31;
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
            channelNames() {
                let names = {};
                if (!this.channels.list) {
                    return names;
                }
                this.channels.list.forEach(channel => {
                    names[channel.id] = channel.name;
                });
                return names;
            }
        },
        watch: {
            "channels.selected"(newChannels) {
                let countToLoad = 0;
                let loadedCount = 0;
                newChannels.forEach(channelId => {
                    if (!this.programs.cache[channelId]) {
                        countToLoad++;
                    }
                });
                if (countToLoad > 0) {
                    this.programs.loading = true;
                }
                newChannels.forEach(channelId => {
                    if (!this.programs.cache[channelId]) {
                        this.programs.cache[channelId] = [];
                        $.get('/channels/'+channelId+'/programs').done(res => {
                            this.$set(this.programs.cache, channelId, res.data.programs);
                            this.reloadProgramsCache();
                            loadedCount++;
                            if (loadedCount === countToLoad) {
                                this.programs.loading = false;
                            }
                        })
                    } else {
                        loadedCount++;
                    }
                });
                this.reloadProgramsCache();
            }
        },
        methods: {
            reloadProgramsCache() {
                let selected = this.channels.selected;
                let programs = [];
                selected.forEach(channelId => {
                    if (this.programs.cache[channelId]) {
                        programs.push({text: this.channelNames[channelId], children: this.programs.cache[channelId].map(program => {
                            return {
                                id: program.id,
                                text: program.name
                            }
                        })});
                    }
                });
                this.programs.list = programs;
            },
            loadChannels() {
                if (this.showExtended && !this.channels.list && !this.channels.loading) {
                    this.channels.loading = true;
                    $.get((this.isRadio ? '/radio-stations' : '/channels') + '/ajax', {is_radio: this.isRadio}).done(res => {
                        this.channels.list = res.data.channels;
                        this.channels.loading = false;
                    })
                }
            },
            extend() {
                this.showExtended = !this.showExtended;
                this.loadChannels();
            },
            setSort(option) {
                if (this.data.sort === option.key) {
                    this.$set(this.data, 'sort_order', this.data.sort_order === 'desc' ? 'asc' : 'desc')
                } else {
                    this.$set(this.data, 'sort', option.key);
                    this.$set(this.data, 'sort_order', 'desc');
                }
                if (this.showResults) {
                    this.load();
                } else {
                    this.$refs.form.submit();
                }
            },
            load() {
                if (this.data.search) {
                    this.lastSearch = this.data.search;
                }
                this.isLoading = true;
                let data = {}; //this.data;
                if (this.isAdvertising) {
                    data.is_advertising = true;
                } else {
                    if (this.channels.selected.length > 0) {
                        data.channels = this.channels.selected;
                    }
                }
                if (this.data.page) {
                    data.page = this.data.page;
                }
                if (this.data.sort) {
                    data.sort = this.data.sort;
                }
                if (this.data.sort_order) {
                    data.sort_order = this.data.sort_order;
                }
                if (this.data.search) {
                    data.search = this.data.search;
                }
                if (this.isInterprogram) {
                    data.is_interprogram = true;
                } else {
                    if (!this.isAdvertising) {
                        if (this.programs.selected.length > 0) {
                            data.programs = this.programs.selected;
                        }
                    }
                }
                if (!this.datesRange) {
                    if (this.dates.year > 0 || this.dates.month > 0 || this.dates.day > 0) {
                        data.date = {};
                        if (this.dates.year > 0) {
                            data.date.year = this.dates.year;
                        }
                        if (this.dates.month > 0) {
                            data.date.month = this.dates.month;
                        }
                        if (this.dates.day > 0) {
                            data.date.day = this.dates.day;
                        }
                    }
                } else {
                    if (this.range.start || this.range.end) {
                        data.dates_range = {};
                        if (this.range.start) {
                            data.dates_range.start = Math.floor(this.range.start.getTime() / 1000);
                        }
                        if (this.range.end) {
                            data.dates_range.end = Math.floor(this.range.end.getTime() / 1000);
                        }
                    }
                }
                console.log(data, this.dates);
                let params = new URLSearchParams();
                for (let key in data) {
                    if (data[key] !== null) {
                        if (typeof data[key] === "object") {
                            if (Array.isArray(data[key])) {
                                params.set(key, data[key].join(','));
                            } else {
                                for (let subkey in data[key]) {
                                    params.set(key + "." + subkey, data[key][subkey]);
                                }
                            }
                        } else {
                            params.set(key, data[key]);
                        }
                    }
                }
                params = params.toString();
                window.history.replaceState({}, '', `${location.pathname}?${params}`);
                $.post(this.action, data).done((res) => {
                    this.resultsList = res.data.records;
                    this.isLoading = false;
                    window.scrollTo(0, 0);
                })
            },
            onSubmit(e) {
                if (this.showResults) {
                    e.preventDefault();
                    this.data.page = 1;
                    this.load();
                }
            },
            getResults(page) {
                this.data.page = page;
                this.load();
            },
            getDescriptionHighlights(text) {
                const search = this.lastSearch.toLowerCase();
                if (search.length === 0) {
                    return [];
                }
                let lines = text.split("\n");
                lines = lines.filter(line => {
                    return line.toLocaleLowerCase().indexOf(search) !== -1;
                }).map(line => line.trim()).map(line => {
                    const timecodeRegex = /^[0-9.:]+ - (.*)/;
                    let isTimecode = timecodeRegex.test(line);
                    if (isTimecode) {
                        let matches  = timecodeRegex.exec(line);
                        return matches[1];
                    }
                    return line;
                });
                return lines;
            },
            getHighlights(text, limitLength = false) {
                if (!text) {
                    return '';
                }
                text = text.replace(/<\/?[^>]+(>|$)/g, "");

                const textLength = text.length;
                const search = this.lastSearch.toLowerCase();

                if (search.length === 0) {
                    return text;
                }
                let lowercaseText = text.toLowerCase();

                const startReplacement = '<span class="highlight">';
                const endReplacement = '</span>';
                const offsetCount = startReplacement.length + endReplacement.length;
                const maxTextSize = 250;

                let index = 0;
                let offset = 0;

                let firstMatch = lowercaseText.indexOf(search);
                if (firstMatch !== -1) {
                    if (limitLength && text.length > maxTextSize) {
                        const start = (firstMatch - maxTextSize / 2) > 0 ? (firstMatch - maxTextSize / 2) : 0;
                        const end = firstMatch + maxTextSize / 2;
                        text = text.substring(start, end);
                        lowercaseText = lowercaseText.substring(start, end);
                    }
                } else {
                    if (limitLength && text.length > maxTextSize) {
                        text = text.substring(0, maxTextSize) + "...";
                    }
                }

                while (index !== -1) {
                    index = lowercaseText.indexOf(search);
                    if (index !== -1) {
                      //  console.log(text.substr(0, index + offset), index, offset);

                        text = text.substr(0, index + offset) + startReplacement + text.substr(index + offset);
                        text = text.substr(0, index + startReplacement.length + search.length + offset) + endReplacement + text.substr(index + startReplacement.length + search.length + offset);
                        offset += (index + offsetCount + search.length);
                    }
                    lowercaseText = lowercaseText.substring(index + search.length);
               }

                if (limitLength && (firstMatch + maxTextSize / 2) < textLength) {
                    text = text + "...";
                }
                if (limitLength && (firstMatch - maxTextSize / 2) > 0) {
                    text = "..." + text;
                }
                return text;
            },
        },
        mounted() {
            if (this.data.search) {
                this.lastSearch = this.data.search;
            }
            ['year', 'month', 'day'].forEach(key => {
                if (this.data['date_' + key]) {
                    this.$set(this.dates, key, this.data['date_' + key]);
                    this.data['date_' + key] = undefined;
                }
            });
            if (this.data.dates_range_start) {
                this.range.start = new Date(this.data.dates_range_start * 1000);
                this.datesRange = true;
            }
            if (this.data.dates_range_end) {
                this.range.end = new Date(this.data.dates_range_end * 1000);
                this.datesRange = true;
            }
            if (this.data.is_interprogram) {
                this.isInterprogram = this.data.is_interprogram;
            }
            if (this.data.is_advertising) {
                this.isAdvertising = this.data.is_advertising;
            }
            if (this.data.channels) {
                this.channels.selected = this.data.channels.split(",");
            }
            if (this.data.programs) {
                this.programs.selected = this.data.programs.split(",");
            }
            if (this.showExtended) {
                this.loadChannels();
            }
            this.loaded = true;
        },
        data() {
            return {
                loaded: false,
                isAdvertising: false,
                isInterprogram: false,
                datesRange: false,
                range: {
                    start: null,
                    end: null
                },
                dates: {
                    year: -1,
                    month: -1,
                    day: -1
                },
                programs: {
                    list: [],
                    cache: {},
                    loading: false,
                    selected: [],
                },
                channels: {
                    loading: false,
                    list: null,
                    selected: [],
                    selectOptions
                },
                showExtended: this.showResults,
                isLoading: false,
                resultsList: this.results,
                lastSearch: '',
                data: JSON.parse(JSON.stringify(this.params)),
                sortOptions: [
                    {
                        title: 'Дате выхода', key: 'date'
                    },
                    {
                        title: 'Дате заливки', key: 'created_at'
                    }
                ],
                disabledDates: {
                    to: new Date(1950, 0, 1),
                    from: new Date(2009, 0, 1),
                }
            }
        },
        props: ['action', 'params', 'showResults', 'results', 'isRadio'],
    }
</script>
