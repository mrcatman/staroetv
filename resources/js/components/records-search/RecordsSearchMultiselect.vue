<template>
    <Preloader v-if="loading || loadingItems"/>
    <div class="multiselect">
        <div class="input-container ">
            <div class="input-container__inner">
                <input ref="title" class="input" placeholder="Поиск"
                       v-model="search"/>
            </div>
        </div>
        <div @scroll="onItemsScroll" class="multiselect__items" :class="{'multiselect__items--show-all': showAll || search.length}">
            <div class="multiselect__nothing-found" v-if="!loading && !filteredItems.length">Ничего не найдено</div>
            <label v-show="(showAll || search.length || $index < 10) && item.name?.length" v-for="(item, $index) in filteredItems"
                   :key="item.id" class="input-container input-container--checkbox multiselect__item">
                <input type="checkbox" v-model="model" :value="item.id">
                <div class="input-container--checkbox__element"></div>
                <div class="multiselect__item__texts">
                    <div class="multiselect__item__name"> {{ item.name }}</div>
                    <div class="multiselect__item__description">{{ item.description }}</div>
                </div>
                <div  class="multiselect__item__count">{{ (counts ? counts[item.id] : item.count) }}</div>
            </label>
        </div>
        <!--
        <a v-if="!search.length" class="button button--block" @click="showAll = !showAll">{{ showAll ? 'Свернуть' : 'Показать все' }}</a>
        -->
    </div>
</template>
<style lang="scss" scoped>
.multiselect {
    display: flex;
    flex-direction: column;
    gap: .5em;

    &__items {
        display: flex;
        flex-direction: column;
        gap: .5em;

        &--show-all {
            max-height: 20em;
            overflow: auto;
        }
    }
    &__nothing-found {
        color: var(--text-lighter);
    }

    &__item {
        height: unset;

        &__description {
            font-size: .75em;
        }

        &__name {
            font-weight: 600;
        }
        &__count {
            margin-left: auto;
            padding: 0 .5em;
            font-size: 1.125em;
            color: var(--text-lightest);
        }
    }
}
</style>
<script lang="ts" setup>
import {computed, ref, watch} from "vue";
import Preloader from "@/components/Preloader.vue";
import { type SearchRecordsCounts } from '../RecordsSearch.vue';
import ScrollEvent = JQuery.ScrollEvent;

export interface MultiselectItem {
    name: string,
    id: string | number,
    description?: string,
    search?: string[]
    count?: number,
}

const props = defineProps<{
    getPage?: (page, term) => Promise<MultiselectItem[]>,
    items?: MultiselectItem[],
    counts?: SearchRecordsCounts,
    loading?: boolean
}>();

const model = defineModel<string[] | number[]>();
const showAll = ref<boolean>(true);
const search = ref<string>('');

const filteredItems = computed(() => {
    if (props.getPage) {
        return loadedItems.value;
    }

    let items = props.items;
    if (props.counts) {
        items = [
            ...items.filter(item => props.counts[item.id]),
            ...items.filter(item => !props.counts[item.id]),
        ];
    }

    if (!search.value.length) {
        return items;
    }
    const _search = search.value.toLocaleLowerCase();
    return items.filter(item => {
        return !!(item.search ? item.search.find(s => s.includes(_search)) : item.name.includes(_search));
    })
})

const loadedItems = ref<MultiselectItem[]>([]);
const loadingItems = ref<boolean>(false);
const currentPage = ref<number>(1);

const loadPage = (reset: boolean = false) => {
    if (!props.getPage) {
        return;
    }

    loadingItems.value = true;
    props.getPage(currentPage.value, search.value).then(items => {
        loadedItems.value = reset ? [...items] : [...loadedItems.value, ...items];
        currentPage.value++;

        loadingItems.value = false;
    }).catch(() => {
        loadingItems.value = false;
    })
}

props.getPage && loadPage();
const onItemsScroll = (e: ScrollEvent) => {
    if (!props.getPage || loadingItems.value) {
        return;
    }

    if (e.target.scrollHeight - e.target.scrollTop > e.target.clientHeight + 1) {
        return;
    }
    loadPage();
}

let searchDebounce;
watch(() => search.value, () => {
    if (!props.getPage) {
        return;
    }
    clearTimeout(searchDebounce);
    searchDebounce = setTimeout(resetItems, 500);
})

const resetItems = () => {
    currentPage.value = 1;
    loadPage(true);
}
watch(() => props.items, () => {
    if (props.getPage) {
        resetItems();
    }
});

defineExpose({resetItems});
</script>
