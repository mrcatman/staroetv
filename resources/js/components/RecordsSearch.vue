<template>
    <div class="row row--align-start records-search">
        <div class="col col--sidebar records-search__filters" :class="showFilters ? 'records-search__filters--opened' : ''">
            <div class="box">
                <div class="box__inner">
                    <div class="form__content records-search__filters__inner">
                        <div class="form__content records-search__filters__main">
                            <div
                                class="radio-buttons radio-buttons--tabs radio-buttons--tabs-one-line records-search__type">
                                <label class="radio-button radio-button--tabs">
                                    <input type="radio" v-model="form.is_radio" name="is_radio" :value="false"/>
                                    <div class="radio-button--tabs__variant">ТВ</div>
                                </label>
                                <label class="radio-button radio-button--tabs">
                                    <input type="radio" v-model="form.is_radio" name="is_radio" :value="true"/>
                                    <div class="radio-button--tabs__variant">Радио</div>
                                </label>
                            </div>

                            <div class="input-container records-search__input-container">
                                <div class="input-container__inner">
                                    <input ref="title" class="input" placeholder="Поиск по названиям, описаниям и т.д."
                                           v-model="form.search" @keyup.enter="reload()"/>
                                </div>
                            </div>
                        </div>


                        <a class="button button--light button--block records-search__toggle"
                           @click="showFilters = !showFilters">
                            <template v-if="showFilters">
                                <i class="fa fa-chevron-up"></i> Свернуть фильтры
                            </template>
                            <template v-else>
                                <i class="fa fa-chevron-down"></i> Развернуть фильтры
                            </template>
                        </a>

                        <component :is="isMobile ? 'div' : 'template'"
                                   :class="showFilters ? 'form__content records-search__filters__advanced' : ''"
                                   v-if="showFilters">
                            <component :is="isMobile ? 'div' : 'template'"
                                       :class="showFilters ? 'form__content records-search__filters__advanced__inner' : ''">

                                <records-search-filter
                                    v-if="!commercials"
                                    title="Тип записи"
                                    v-model:opened="opened.type"
                                    :show-reset="form.type !== null"
                                    @reset="() => form.type = null"
                                >
                                    <div class="radio-buttons">
                                        <label class="radio-button">
                                            <input type="radio" v-model="form.type" name="type" :value="null"/>
                                            <div class="radio-button__circle"></div>
                                            <div class="radio-button__text">Все</div>
                                        </label>
                                        <label class="radio-button">
                                            <input type="radio" v-model="form.type" name="type" value="programs"/>
                                            <div class="radio-button__circle"></div>
                                            <div class="radio-button__text">Передача</div>
                                        </label>
                                        <label class="radio-button">
                                            <input type="radio" v-model="form.type" name="type" value="interprogram"/>
                                            <div class="radio-button__circle"></div>
                                            <div class="radio-button__text">
                                                {{
                                                    form.is_radio ? 'Джингл, перебивка и т.д.' : 'Заставка канала, анонс и т.д.'
                                                }}
                                            </div>
                                        </label>
                                        <label class="radio-button">
                                            <input type="radio" v-model="form.type" name="type" value="advertising"/>
                                            <div class="radio-button__circle"></div>
                                            <div class="radio-button__text">Рекламный ролик</div>
                                        </label>
                                        <label class="radio-button">
                                            <input type="radio" v-model="form.type" name="type" value="program-design"/>
                                            <div class="radio-button__circle"></div>
                                            <div class="radio-button__text">Заставка передачи</div>
                                        </label>
                                        <label class="radio-button">
                                            <input type="radio" v-model="form.type" name="type" value="other"/>
                                            <div class="radio-button__circle"></div>
                                            <div class="radio-button__text">Прочее</div>
                                        </label>
                                    </div>
                                </records-search-filter>
                                <records-search-filter
                                    v-show="form.type === 'advertising'"
                                    title="Тип рекламы"
                                    v-model:opened="opened.advertising_type"
                                    :show-reset="form.advertising_type !== null"
                                    @reset="() => form.advertising_type = null"
                                >
                                    <div class="radio-buttons">
                                        <label class="radio-button">
                                            <input type="radio" v-model="form.advertising_type" name="advertising_type"
                                                   :value="null">
                                            <div class="radio-button__circle"></div>
                                            <div class="radio-button__text">Любой</div>
                                        </label>
                                        <label class="radio-button" v-for="type in categoriesStore.advertisingTypes">
                                            <input type="radio" v-model="form.advertising_type" name="advertising_type"
                                                   :value="type.id">
                                            <div class="radio-button__circle"></div>
                                            <div class="radio-button__text">{{ type.text }}</div>
                                        </label>
                                    </div>
                                </records-search-filter>

                                <records-search-filter
                                    title="Дата выхода"
                                    v-model:opened="opened.date"
                                    :show-reset="form.date.year > 0 || form.date.year_start > 0 || form.date.year_end > 0"
                                    @reset="() => form.date = defaultDate()"
                                >
                                    <date-select
                                        v-model="form.date"
                                        :range="form.date.range"
                                        :only-years="['advertising', 'interprogram', 'program-design'].includes(form.type)"
                                        search
                                    />

                                    <div class="input-container__toggle-buttons">
                                        <a class="input-container__toggle-button" v-if="!form.date.range"
                                           @click="form.date.range = true">Указать временной промежуток
                                        </a>
                                        <a class="input-container__toggle-button" v-else
                                           @click="form.date.range = false">
                                            Указать конкретную дату
                                        </a>
                                    </div>
                                    <div class="categories-list categories-list--multiline records-search__periods">
                                        <a class="category" v-for="period in periods"
                                           @click="setPeriod(period)">{{ period.name }}</a>
                                    </div>
                                </records-search-filter>
                                <records-search-filter
                                    v-show="form.type !== 'advertising' && form.type !== 'other'"
                                    v-model:opened="opened.channels"
                                    :title="form.is_radio ? 'Радиостанции' : 'Каналы'"
                                    :show-reset="!!form.channels.length"
                                    @reset="() => form.channels = []"
                                >
                                    <records-search-multiselect
                                        v-model="form.channels"
                                        :items="channels"
                                        :counts="counts.channels"
                                        :loading="channelsStore.loading"
                                    />
                                </records-search-filter>

                                <records-search-filter
                                    v-show="form.channels.length && form.type !== 'interprogram'"
                                    title="Программы"
                                    v-model:opened="opened.programs"
                                    :show-reset="!!form.programs.length"
                                    @reset="() => form.programs = []"
                                >
                                    <records-search-multiselect
                                        v-model="form.programs"
                                        :items="programs"
                                        :counts="counts.programs"
                                        :loading="programsStore.loading"
                                    />
                                </records-search-filter>

                                <records-search-filter
                                    v-show="form.type === 'advertising'"
                                    title="Категории"
                                    v-model:opened="opened.advertising_categories"
                                    :show-reset="!!form.advertising_categories.length"
                                    @reset="() => form.advertising_categories = []"
                                >
                                    <records-search-multiselect
                                        ref="multiselect_categories"
                                        v-model="form.advertising_categories"
                                        :counts="counts?.advertising_categories"
                                        :items="itemsFromCountsCategories"
                                        :get-page="!counts?.advertising_categories ? (page, term) => getAdvertisingCategories(page, term) : null"
                                    />
                                </records-search-filter>

                                <records-search-filter
                                    v-show="form.type === 'advertising'"
                                    title="Бренды"
                                    v-model:opened="opened.advertising_brands"
                                    :show-reset="!!form.advertising_brands.length"
                                    @reset="() => form.advertising_brands = []"
                                >
                                    <records-search-multiselect
                                        ref="multiselect_brands"
                                        v-model="form.advertising_brands"
                                        :counts="counts?.advertising_brands"
                                        :items="itemsFromCountsBrands"
                                        :get-page="!counts?.advertising_brands ? (page, term) => getAdvertisingBrands(page, term) : null"
                                    />
                                </records-search-filter>

                                <records-search-filter
                                    v-show="form.type === 'advertising'"
                                    title="Регионы"
                                    v-model:opened="opened.advertising_regions"
                                    :show-reset="!!form.advertising_regions.length"
                                    @reset="() => form.advertising_regions = []"
                                >
                                    <records-search-multiselect
                                        ref="multiselect_regions"
                                        v-model="form.advertising_regions"
                                        :counts="counts?.advertising_regions"
                                        :items="itemsFromCountsRegions"
                                        :get-page="!counts?.advertising_regions ? (page, term) => getAdvertisingRegions(page, term) : null"
                                    />
                                </records-search-filter>

                                <records-search-filter
                                    v-show="form.type === 'advertising'"
                                    title="Страны"
                                    v-model:opened="opened.advertising_countries"
                                    :show-reset="!!form.advertising_countries.length"
                                    @reset="() => form.advertising_countries = []"
                                >
                                    <records-search-multiselect
                                        ref="multiselect_countries"
                                        v-model="form.advertising_countries"
                                        :counts="counts?.advertising_countries"
                                        :items="itemsFromCountsCountries"
                                        :get-page="!counts?.advertising_countries ? (page, term) => getAdvertisingCountries(page, term) : null"
                                    />
                                </records-search-filter>
                            </component>
                            <div class="records-search__filters__advanced__bottom">
                                <a class="button button--big button--block" @click="load(true)">
                                    <i class="fa fa-search"></i>
                                    Поиск
                                </a>
                            </div>
                        </component>
                    </div>
                </div>
            </div>
        </div>
        <div class="col col--2-5 records-search__results">
            <response v-if="error"
                      :data="{status: 0, text: 'Ошибка сервера, попробуйте позже или напишите на форуме'}"/>
            <template v-else>
                <!--
                <div class="box">
                    <div class="box__inner">
                        <div class="records-search__result" v-if="showResults">

                        </div>
                    </div>
                </div>
                -->
                <div class="box box--dark">
                    <div class="box__inner">
                        <preloader v-if="loading"/>
                        <div class="records-list__filters">
                            <div class="records-list__sort">
                                <div class="top-list records-list__sort__items records-search__sort">
                                    <span class="records-search__total" v-if="results?.total > 0">
                                         {{
                                            declination(results.total, ['Найдена [number] запись', 'Найдено [number] записи', 'Найдено [number] записей'])
                                        }}
                                    </span>
                                    <a class="top-list__item"
                                       :class="{'top-list__item--active': form.sort === option.key}"
                                       @click="setSort(option)" :key="$index" v-for="(option, $index) in sortOptions">
                                        {{ option.title }}
                                        <template v-if="form.sort === option.key">
                                            {{ form.sort_order === 'asc' ? '↑' : '↓' }}
                                        </template>
                                    </a>
                                </div>

                            </div>
                        </div>

                        <div class="records-search__nothing-found" v-if="results?.data?.length === 0">
                            По вашему запросу ничего не найдено
                        </div>
                        <div class="programs-list records-search__programs" v-if="displayPrograms?.length">
                            <programs-item v-for="program in displayPrograms" :program="program"/>
                        </div>

                        <div class="records-list" :class="{'records-list--thumbs': !form.is_radio}">
                            <records-item v-for="record in results.data" :key="record.id" :record="record">
                                <template #title>
                                    <span v-html="highlight(record.title, search)"></span>
                                </template>
                                <template #description>
                                    <div class="record-item__timecodes">
                                        <a :href="`${record.url}?start=${getStartTimeFromTimecodeLine(line)}`"
                                           class="record-item__timecodes__line"
                                           v-for="(line, $index) in highlightDescription(record.description, search)"
                                           :key="$index" v-html="highlight(line, search, true)">
                                        </a>
                                    </div>
                                </template>
                            </records-item>
                        </div>
                    </div>
                    <div class="box__pager" v-show="results?.last_page > 1">
                        <pagination
                            :limit="3"
                            :data="results"
                            @pagination-change-page="(page) => setPage(page)"
                        />
                    </div>
                </div>
            </template>
        </div>
    </div>


</template>
<style lang="scss" scoped>
@use "../../sass/mixins" as *;

.records-search {
    @include mobile() {
        min-height: 100vh;
    }
    &__filters {
        height: calc(100vh - 8.5em);
        padding: 0 0 0 4em;
        margin: 0 0 0 -4em;
        position: sticky;
        align-self: flex-start;
        top: 5em;
        overflow-y: auto;
        @include mobile() {
            position: fixed;
            display: flex;
            flex: unset;
            height: unset;
            width: 100%;
            max-height: calc(100vh - 3.25em);
            left: 0;
            top: 3.25em;
            z-index: 10;
            padding: 0;
            margin: 0 !important;
            .box {
                margin: 0;
                border-top-left-radius: 0;
                border-top-right-radius: 0;
                &__inner {
                    height: 100%;
                    box-sizing: border-box;
                }
            }
        }

        &--opened {
            height: 100%;
            display: flex;
        }

        &__main {
                flex-direction: row;
        }

        &__inner {
            @include mobile() {
                height: 100%;
                overflow: hidden;
            }
        }

        &__advanced {
            @include mobile() {
                align-items: stretch;
                overflow: hidden;
                height: 100%;
                flex: 1;
            }

            &__inner {
                @include mobile() {
                    height: 100%;
                    overflow: auto;
                    align-items: stretch;
                    flex: 1;
                }
            }
            &__bottom {
                display: none;
                @include mobile() {
                    display: block;
                }
            }
        }
    }


    &__type {
        @include mobile() {
            width: 8em;
        }
    }

    &__toggle {
        display: none;
        @include mobile() {
            display: block;
        }
    }

    &__sort {
        @include mobile() {
            display: flex;
            width: 100%;
            font-size: 1em;
        }
    }

    &__results {
        @include mobile() {
            margin-top: 8em !important;
        }
    }

    &__total {
        margin-right: auto;
    }

    &__programs {
        margin-bottom: calc(var(--col-margin) * 2);
    }

    &__periods {
        font-size: .875em;
        margin: .5em 0;
        padding: 0;
    }
}
</style>
<script lang="ts" setup>
import { computed, ref, useTemplateRef, watch } from "vue";
import { Bootstrap5Pagination as Pagination } from 'laravel-vue-pagination';

import { useChannelsStore } from "@/stores/channels";
import { useProgramsStore } from "@/stores/programs";
import { updateQueryString } from "@/utils/query-string";
import { getStartTimeFromTimecodeLine, highlight, highlightDescription } from "@/utils/highlight";
import { defaultDate } from "@/utils/dates";
import { useCategoriesStore } from "@/stores/categories";

import RecordsSearchMultiselect, { type MultiselectItem } from './records-search/RecordsSearchMultiselect.vue';
import DateSelect from "@/components/DateSelect.vue";
import RecordsSearchFilter from "@/components/records-search/RecordsSearchFilter.vue";
import ProgramsItem from "@/components/programs/ProgramsItem.vue";
import { declination } from "@/utils/numbers";
import RecordsItem from "@/components/records/RecordsItem.vue";
import { isMobile } from "@/utils/mobile";

interface SearchForm {
    page: number,
    is_radio: boolean,
    search: string,
    type?: Records.Type,
    channels: number[],
    programs: number[],
    sort?: string,
    sort_order: 'asc' | 'desc'
    date: Common.Date,

    advertising_type?: number,
    advertising_brands?: string[]
    advertising_countries?: string[]
    advertising_regions?: string[]
    advertising_categories?: string[]
}

export interface SearchRecordsCounts {
    [key: number]: number
}

type SearchRecordsCountsList = {
    channels?: SearchRecordsCounts
    programs?: SearchRecordsCounts,
    advertising_brands?: SearchRecordsCounts,
    advertising_categories?: SearchRecordsCounts,
    advertising_regions?: SearchRecordsCounts,
    advertising_countries?: SearchRecordsCounts,
}

const props = defineProps<{
    results: Forms.PaginatedResponse<Models.Record[]>,
    params: Partial<SearchForm>,
    counts: SearchRecordsCountsList,
    recommendedPrograms?: Models.Program[],
    periods?: Common.Period[],
    commercials?: boolean,
}>();

const results = ref<any>(props.results);
const counts = ref<SearchRecordsCountsList>(props.counts);
const displayPrograms = ref<Models.Program[]>(props.recommendedPrograms);

const channelsStore = useChannelsStore();
const programsStore = useProgramsStore();
const categoriesStore = useCategoriesStore();

const search = ref<string>(props.params.search ?? '');

const form = ref<SearchForm>({
    page: 1,
    search: '',
    type: props.commercials ? 'advertising' : null,
    channels: [],
    programs: [],
    sort: 'created_at',
    sort_order: 'desc',
    advertising_type: null,
    advertising_brands: [],
    advertising_countries: [],
    advertising_regions: [],
    advertising_categories: [],

    ...props.params,
    is_radio: !!props.params?.is_radio,
    date: {
        ...defaultDate(),
        ...props.params?.date
    },
});

const prevForm = ref<SearchForm>(form.value);

const loading = ref<boolean>(false);
const error = ref<boolean>(false);

const load = (loadMobile: boolean = false) => {
    if (showFilters.value && isMobile()) {
        if (loadMobile) {
            prevForm.value = {
                ...prevForm.value,
                ...form.value
            };
            showFilters.value = false;
        } else {
            return;
        }
    }

    if (loading.value) {
        return;
    }

    const data = form.value;
    if (data.search.length) {
        search.value = data.search;
    }
    loading.value = true;

    updateQueryString(data);
    $.post(route('records.search'), data).done((res) => {
        error.value = false;
        results.value = res.data.results;
        counts.value = res.data.counts;
        displayPrograms.value = res.data.programs;

        loading.value = false;
        window.scrollTo(0, 0);
    }).catch(() => {
        error.value = true;
        loading.value = false;
        window.scrollTo(0, 0);
    })
}

const reload = () => {
    form.value.page = 1;
    load();
}

const setPage = (page: number) => {
    form.value.page = page;
    load();
}

watch(() => [
    form.value.is_radio,
    form.value.programs,
    form.value.date.year, form.value.date.month, form.value.date.day,
    form.value.date.year_start, form.value.date.month_start, form.value.date.day_start,
    form.value.date.year_end, form.value.date.month_end, form.value.date.day_end,
    form.value.advertising_brands, form.value.advertising_countries, form.value.advertising_regions, form.value.advertising_categories
], reload);

watch(() => form.value.type, (type) => {
    if (type !== 'advertising') {
        form.value.advertising_type = null;
        form.value.advertising_brands = [];
        form.value.advertising_countries = [];
        form.value.advertising_regions = [];
        form.value.advertising_categories = [];
    }

    if (type === 'advertising' || type === 'other') {
        form.value.channels = [];
        form.value.programs = [];

        categoriesStore.load();
    }
    reload();
})

watch(() => form.value.channels, (channelIds, oldChannelIds) => {
    const diff = oldChannelIds.filter(x => !channelIds.includes(x));
    if (diff.length) {
        const excludeProgramIds = diff.flatMap(channelId => (programsStore.programs[channelId] ?? []).map(program => program.id) ?? []);
        form.value.programs = form.value.programs.filter(programId => !excludeProgramIds.includes(programId))
    }
    reload();
});


interface SortOption {
    title: string,
    key: string
}

const sortOptions: SortOption[] = [
    {
        title: 'Дата эфира', key: 'supposed_date'
    },
    {
        title: 'Дата заливки', key: 'created_at'
    }
];
const setSort = (sort: SortOption) => {
    if (form.value.sort === sort.key) {
        form.value.sort_order = form.value.sort_order === 'desc' ? 'asc' : 'desc';
    } else {
        form.value.sort = sort.key;
        form.value.sort_order = 'desc';
    }
    load();
}

const channels = computed<MultiselectItem[]>(() => {
    return (form.value.is_radio ? channelsStore.radioStations : channelsStore.channels).map(channel => {
        const names = [channel.name.toLocaleLowerCase()];
        const description = [];
        channel.names.forEach(name => {
            if (name.name?.length) {
                names.push(name.name.toLocaleLowerCase());
                if (name.name != channel.name && !description.includes(name.name)) {
                    description.push(name.name);
                }
            }
            name.alternatives?.length && name.alternatives.forEach(alternative => names.push(alternative.toLocaleLowerCase()));
        })
        return {
            id: channel.id,
            name: channel.name,
            description: description.join(', '),
            search: names
        }
    })
})


const programs = computed<MultiselectItem[]>(() => {
    return form.value.channels.flatMap(channelId => {
        const channelPrograms = programsStore.programs[channelId] ?? [];

        return channelPrograms.map(program => {
            const names = [program.name.toLocaleLowerCase()];

            return {
                id: program.id,
                name: program.name,
                description: '',
                search: names,
            }
        })
    }).sort((a, b) => a.name.localeCompare(b.name));
});

const opened = ref({
    type: true,
    date: true,
    channels: false,
    programs: false,
    advertising_type: true,
    advertising_brands: false,
    advertising_countries: false,
    advertising_regions: false,
    advertising_categories: false,
});

watch(() => opened.value.channels, (channelsOpened) => {
    channelsOpened && channelsStore.load();
})
watch(() => opened.value.programs, (programsOpened) => {
    programsOpened && form.value.channels.forEach(channelId => programsStore.load(channelId));
})

if (props.params.channels?.length) opened.value.channels = true;
if (props.params.programs?.length) opened.value.programs = true;
if (props.params.type === 'advertising' || props.commercials) categoriesStore.load();

const getMultiselectItems = (_route: string, page: number, term: string): Promise<MultiselectItem[]> => {
    return new Promise(resolve => {
        $.get(route(_route, {
            page,
            term,
            advertising_type: form.value.advertising_type,
            is_radio: form.value.is_radio,
            for_search: true
        })).then(({data}) => {
            resolve(data);
        });
    })
}

const getAdvertisingBrands = (page: number, term?: string): Promise<MultiselectItem[]> => {
    return getMultiselectItems('records.autocomplete.commercials-brands', page, term);
}

const getAdvertisingCountries = (page: number, term?: string): Promise<MultiselectItem[]> => {
    return getMultiselectItems('records.autocomplete.countries', page, term);
}

const getAdvertisingRegions = (page: number, term?: string): Promise<MultiselectItem[]> => {
    return getMultiselectItems('records.autocomplete.regions', page, term);
}

const getAdvertisingCategories = (page: number, term?: string): Promise<MultiselectItem[]> => {
    return getMultiselectItems('records.autocomplete.categories', page, term);
}


const brandsMultiselect = useTemplateRef<typeof RecordsSearchMultiselect>('multiselect_brands');
const countriesMultiselect = useTemplateRef<typeof RecordsSearchMultiselect>('multiselect_countries');
const regionsMultiselect = useTemplateRef<typeof RecordsSearchMultiselect>('multiselect_regions');
const categoriesMultiselect = useTemplateRef<typeof RecordsSearchMultiselect>('multiselect_categories');

watch(() => form.value.advertising_type, () => {
    form.value.advertising_brands = [];

    brandsMultiselect.value?.resetItems();
    countriesMultiselect.value?.resetItems();
    regionsMultiselect.value?.resetItems();
    categoriesMultiselect.value?.resetItems();

    reload();
})

const itemsFromCounts = (counts: SearchRecordsCountsList): MultiselectItem[] => {
    if (counts) {
        return Object.keys(counts).map((name) => {
            return {
                id: name,
                name,
                search: [name.toLocaleLowerCase()]
            }
        })
    }
    return [];
}

const itemsFromCountsBrands = computed(() => {
    return itemsFromCounts(counts.value?.advertising_brands);
})
const itemsFromCountsCategories = computed(() => {
    return itemsFromCounts(counts.value?.advertising_categories);
})
const itemsFromCountsCountries = computed(() => {
    return itemsFromCounts(counts.value?.advertising_countries);
})
const itemsFromCountsRegions = computed(() => {
    return itemsFromCounts(counts.value?.advertising_regions);
})

const setPeriod = (period: Common.Period) => {
    form.value.date = {
        ...defaultDate(),
        range: true,
        year_start: period.years[0] ?? -1,
        year_end: period.years[1] ?? -1,
    }
}

const showFilters = ref<boolean>(!isMobile());
watch(showFilters, () => {
   if (showFilters.value) {
       prevForm.value = {
           ...prevForm.value,
           ...form.value
       };
   } else {
       form.value = {
           ...form.value,
           ...prevForm.value
       }
   }
});
</script>
