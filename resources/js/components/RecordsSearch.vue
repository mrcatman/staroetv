<template>
    <form @submit="onSubmit" ref="form" method="GET" :action="action" class="records-search--container">
        <div class="records-search">
            <div class="records-search__inner">
                <div class="records-search__title">Поиск по записям</div>
                <div class="records-search__input-container">
                    <input v-model="data.search" class="input" placeholder="" name="search"/>
                </div>
                <button class="button button--light">Найти</button>
                <a class="records-search__expand" @click="extend()">Расширенный поиск</a>
            </div>
            <div class="records-search__extended" v-show="showExtended">
                <div class="records-search__extended__row">
                    <div class="inputs-line">
                        <div class="inputs-line__item">
                            <div class="inputs-line__item__title">{{isRadio ? "Радиостанции" : "Каналы"}}</div>
                            <div class="input-container__overlay-outer">
                                <div class="input-container__disabled-overlay" v-if="isAdvertising"></div>
                                <div class="input-container__loading-text" v-if="channels.loading">Загрузка...</div>
                                <select2 name="channels" :customOptions="channels.selectOptions" v-if="!channels.loading" multiple :options="channels.list" v-model="channels.selected"></select2>
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
                                <select2 name="programs" v-if="!programs.loading" multiple :options="programs.list" v-model="programs.selected"></select2>
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
                <div class="box">
                    <div class="box__inner">
                        <div class="records-list__pager-container records-list__pager-container--top" v-show="resultsList.last_page > 1">
                            <pagination :limit="3" :data="resultsList" @pagination-change-page="getResults"></pagination>
                        </div>
                        <div class="records-list">
                            <a :href="record.url" v-for="(record) in resultsList.data" :key="record.id" class="record-item">
                                <div class="record-item__cover" :style="{backgroundImage: `url(${record.cover})`}"></div>
                                <div class="record-item__texts">
                                    <span class="record-item__title">
                                        {{record.title}}
                                    </span>
                                    <div class="record-item__info">
                                        <span class="record-item__date"><i class="fa fa-calendar"></i>{{record.created_at}}</span>
                                        <span class="record-item__views"><i class="fa fa-eye"></i>{{record.views}}</span>
                                    </div>
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
    .records-search {
        background: linear-gradient(#fefefe, #d5d5d5);
        border-top: 1px solid #fff;
        box-shadow: 0 0.25em 1em rgba(0, 0, 0, 0.2901960784313726);
        &__inner {
            padding: 1em;
            display: flex;
            align-items: center;
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
            color: #333;
            cursor: pointer;
            border-bottom: 1px dashed #333;
            margin: 0 0 0 1em;

            &:hover {
                color: #777;
            }
        }

        .select2 {
            display: block;
            width: 100%!important;
        }

        &__extended {
            border-top: 1px solid #ddd;
            background: linear-gradient(to bottom, #fff, #fafafa);
            &__row {
                flex: 1;
                padding: 1em;
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
        &__sort {
            background: #eee;
            padding: 1em;
            &__title {
                font-weight: 600;
            }

            &__option {
                margin: 0 .5em 0 0;
                color: #8b5428;

                &--active {
                    border-bottom: 1px dashed;
                }

                &__title {
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
                this.channels.list.forEach(channel => {
                    names[channel.id] = channel.name;
                });
                return names;
            }
        },
        watch: {
            "channels.selected"(newChannels) {
                newChannels.forEach(channelId => {
                    if (!this.programs.cache[channelId]) {
                        this.programs.cache[channelId] = [];
                        $.get('/channels/'+channelId+'/programs').done(res => {
                            this.$set(this.programs.cache, channelId, res.data.programs);
                            this.reloadProgramsCache();
                        })
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
                this.isLoading = true;
                let data = this.data;
                if (this.isAdvertising) {
                    data.is_advertising = true;
                } else {
                    if (this.channels.selected.length > 0) {
                        data.channels = this.channels.selected;
                    }
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
                let params = new URLSearchParams();
                for (let key in data) {
                    if (data[key] !== null) {
                        if (typeof data[key] === "object") {
                            for (let subkey in data[key]) {
                                params.set(key + "." + subkey, data[key][subkey]);
                            }
                        } else {
                            params.set(key, data[key]);
                        }
                    }
                }
                params = params.toString();
                window.history.replaceState({}, '', `${location.pathname}?${params}`);
                $.post(this.action, data).done((res) => {
                    this.resultsList = res.records;
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
            }
        },
        mounted() {
            if (this.showExtended) {
                this.loadChannels();
            }
        },
        data() {
            return {
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