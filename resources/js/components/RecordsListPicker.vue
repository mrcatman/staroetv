<template>

    <snackbar ref="snackbar"></snackbar>
    <input type="hidden" v-if="name" :name="name" :value="dataJson">
    <modal ref="searchRecordsModal" title="Поиск записей" >
        <form class="records-list-picker__search">
            <Preloader v-if="searchLoading" />
            <input placeholder="Поиск" class="input" v-model="search"/>
            <div class="tabs">
                <a class="tab" :class="{'tab--active': searchType === 'filtered'}" @click="searchType = 'filtered'">Рекомендуемые</a>
                <a class="tab" :class="{'tab--active': searchType === 'all'}" @click="searchType = 'all'">Все записи</a>
            </div>
            <div class="records-list records-list-picker__search__list" v-if="searchResults?.data">
                <records-item
                    v-for="record in searchResults.data"
                    :record="record"
                    :disable-links="true"
                    @click="onSelectRecord(record)"
                    :class="{'record-item--selected': selectedIds.indexOf(record.id) !== -1}"
                />
            </div>
            <div v-if="searchResults" class="modal-window__pager records-list-picker__search__pager">
                <div class="pager-container">
                    <pagination :limit="3" :data="searchResults" @pagination-change-page="loadSearch"/>
                </div>
            </div>
            <div class="form__bottom records-list-picker__search__submit">
                <a @click="submitSelectedRecords()" class="button button--light">Выбрать</a>
            </div>
        </form>
    </modal>

    <modal ref="addRecordModal" title="Добавить новую запись">
        <record-form
            class="records-list-picker__form"
            @save="onNewRecord"
            :in-modal="true"
            :start-params="startParams"
        />
    </modal>

    <div class="records-list-picker box box--dark">
        <div class="box__heading">
            <div class="box__heading__inner">
                Выбор записей
            </div>

            <div class="box__heading__buttons">
                <div class="buttons-row">
                    <a class="button" @click="showSearch()">
                        <i class="fa fa-film"></i>
                        Выбрать с сайта
                    </a>
                    <a class="button" @click="addRecordModalRef.show()">
                        <i class="fa fa-upload"></i>
                        Загрузить новое видео
                    </a>
                    <a class="button button--light" v-if="annotations" @click="addAnnotation()">
                        <i class="fa fa-list"></i>
                        Добавить аннотацию
                    </a>
                </div>
            </div>
        </div>

        <div ref="items" class="box__inner records-list-picker__items">
            <div class="warning-alert">
                Добавляйте записи, только у которых есть какое-то существенное отличие от остальных.
                Нет смысла грузить 10 одинаковых начал эфира
            </div>
            <div class="records-list__empty" v-if="recordsList.length === 0">Нет записей</div>
            <draggable
                class="records-list"
                v-model="recordsList"
                #item="{element, index}"
                itemKey="id"
            >
                <div class="records-list-picker__item">
                    <div v-if="!element.is_annotation"
                         class="record-item record-item--unselectable"
                         :class="{'records-list-picker__item--updating': element.updating, 'records-list-picker__item--selected': element.is_selected}">
                        <div class="records-list-picker__buttons">
                            <a class="records-list-picker__button" @click="deleteRecord(element)">Удалить</a>
                        </div>
                        <a target="_blank" :href="element.model.full_url" class="record-item__cover"
                           :style="{backgroundImage: `url(${element.model.cover})`}"></a>
                        <div class="record-item__texts">
                            <span class="record-item__title" v-html="element.model.title"></span>
                            <div class="records-list-picker__fields-container">
                                <textarea v-if="recordDescriptions" v-model="element.model.block_description"
                                          class="input"
                                          placeholder="Описание"></textarea>
                                <select @change="updateRecord(element, 'interprogram_type')" class="select-classic"
                                        v-if="interprogramEditor" v-model="element.model.interprogram_type">
                                    <option v-for="type in categoriesStore.interprogramTypes" :value="type.id">
                                        {{ type.text }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div v-else class="records-list-picker__item records-list-picker__annotation">
                        <div class="records-list-picker__buttons">
                            <a class="records-list-picker__button" @click="recordsList.splice(index, 1)">Удалить</a>
                        </div>
                        <input v-model="element.model.title" class="input" placeholder="Заголовок аннотации"/>
                        <textarea v-model="element.model.text" class="input" placeholder="Описание"></textarea>
                    </div>

                </div>
            </draggable>
        </div>
    </div>

</template>
<style lang="scss">
.records-list-picker {
    &__item {
        width: 100%;
    }

    &__items {
        display: flex;
        flex-direction: column;
        gap: 1em;
    }

    &__type-select {
        width: 16em;
        margin: 1em 1em 0;
    }

    &__buttons {
        position: absolute;
        top: .5em;
        right: .75em;
        opacity: .75;
        z-index: 1000;
        cursor: pointer;
        text-align: right;
    }

    &__annotation {
        position: relative;
        display: flex;
        flex-direction: column;
        margin: 1em 0;
    }

    &__button {
        background: var(--box-color-hover-dark);
        color: #fff;
        padding: .25em .5em;
        border-radius: var(--border-radius-small);
        margin: 0 0 .25em;
        display: inline-block;

        &:hover {
            filter: brightness(1.25);
        }
    }

    &__fields-container {
        max-width: calc(100% - 5em);
        margin: .25em .125em;

        .input {
            width: 100%;
        }

        .select-classic {
            width: 50%;
        }

    }

    &__item {
        &:hover {
            background: none !important;
        }

        &--updating {
            opacity: .5;
        }
    }

    .record-item:hover &__delete {
        opacity: 1;
    }

    &__form {
        margin: -2em -1em;
        padding: 2em 1em;
        font-size: .875em;

        .input-container__label {
            min-width: 8.5em;
        }

        .select2 {
            width: 100% !important;
        }

        .record-form__covers {
            margin: .25em -.5em 0 -.25em;
            flex-wrap: nowrap;
            justify-content: space-between;
        }

        .record-form__cover {
            margin: 0 .25em 0 0;
            width: auto;
            height: 4.5em;

            &:hover {
                border: 2px solid rgba(255, 255, 255, 0);
                box-shadow: none;
                filter: brightness(1.1);
            }
        }

        .record-form__player-container {
            position: relative;
            padding-top: 60%;

            &__outer {
                background: none;
                flex-direction: column;
                padding: 0;
            }

            iframe {
                width: 100%;
                height: 100%;
                position: absolute;
                top: 0;
                left: 0;
            }
        }
    }


    &__search {
        height: 100%;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        gap: var(--col-margin);

        &__list {
            max-height: 45em;
            overflow: auto;
            overflow-x: hidden;
        }

    }
}


</style>
<script lang="ts" setup>
import { computed, nextTick, ref, useTemplateRef, watch } from "vue";
import draggable from 'vuedraggable';

import { Bootstrap5Pagination as Pagination } from 'laravel-vue-pagination';
import Modal from './Modal.vue';
import Snackbar from './Snackbar.vue';
import { type SearchForm } from "./RecordsSearch.vue";

import { type RecordsUploadData } from "@/composables/record-form";
import { useCategoriesStore } from "@/stores/categories";
import RecordsItem from "@/components/records/RecordsItem.vue";
import Preloader from "@/components/Preloader.vue";

const categoriesStore = useCategoriesStore();
categoriesStore.load();

type AnnotationOrRecord = {
    is_annotation: boolean,
    updating?: boolean,
    model: Models.Annotation | Models.Record,
}

const props = defineProps<{
    records: Models.Record[],
    annotations: Models.Annotation[],
    interprogramEditor?: boolean,
    recordDescriptions?: boolean,
    name?: string,
    startParams?: Partial<RecordsUploadData>,
    searchParams?: Partial<SearchForm>
}>();

const recordsList = ref<AnnotationOrRecord[]>([...(props.records || []).map(record => {
    return {
        is_annotation: false,
        model: record
    }
}), ...((props.annotations || []).map(annotation => {
    return {
        is_annotation: true,
        model: annotation
    };
}))].sort((a, b) => {
    const orderA = a.model.internal_order ?? a.model.order;
    const orderB = b.model.internal_order ?? b.model.order;
    return orderA - orderB;
}));

const selectedRecords = ref<Models.Record[]>([]);

const dataJson = computed(() => {
    return JSON.stringify(recordsList.value.map(record => {
        return record.is_annotation ? record : {
            is_annotation: false,
            id: record.model.id,
        }
    }))
});

const selectedIds = computed(() => {
    return selectedRecords.value.map(record => record.id);
});

const addAnnotation = async () => {
    recordsList.value.push({
        is_annotation: true,
        model: {
            title: '',
            text: ''
        }
    })

    await nextTick();
    scrollToEnd();
}

const addRecordModalRef = useTemplateRef<typeof Modal>('addRecordModal');
const searchRecordsModalRef = useTemplateRef<typeof Modal>('searchRecordsModal');
const snackbarRef = useTemplateRef<typeof Snackbar>('snackbar');
const itemsRef = useTemplateRef<HTMLElement>('items');

const onNewRecord = (record: Models.Record) => {
    recordsList.value.push({
        is_annotation: false,
        model: record
    });
    addRecordModalRef.value.hide();
}

const updateRecord = (record: AnnotationOrRecord, field: string) => {
    record.updating = true;

    const params = {
        [field]: record.model[field]
    };
    $.post('/records/mass-edit', {
        ids: [record.model.id],
        params
    }).done(res => {
        record.updating = false;
        if (!res.status) {
            snackbarRef.value.show(res);
        }
    })
}

const onSelectRecord = (record: Models.Record) => {
    if (selectedIds.value.indexOf(record.id) === -1) {
        selectedRecords.value.push(record);
    } else {
        selectedRecords.value.splice(selectedIds.value.indexOf(record.id), 1);
    }
}

const deleteRecord = (record: AnnotationOrRecord) => {
    recordsList.value.splice(recordsList.value.map(item => item.model.id).indexOf(record.model.id), 1);
}

let searchTimeout: number;
const search = ref<string>('');
const searchLoading = ref<boolean>(false);
const searchPage = ref<number>(1);
const searchResults = ref<Forms.PaginatedResponse<Models.Record[]>>();
const searchType = ref<'filtered' | 'all'>('filtered');

watch(() => search.value, () => {
    searchTimeout = setTimeout(() => {
        loadSearch(1);
    }, 500)
});
watch(() => searchType.value, () => {
   loadSearch(1);
});

const loadSearch = (page?: number) => {
    searchLoading.value = true;
    if (page) {
        searchPage.value = page;
    }

    const params = {
        ...(searchType.value === 'filtered' ? props.searchParams : {}),
        page: searchPage.value,
        exclude_ids: recordsList.value.map(record => record.model.id).filter(id => !!id),
    }
    if (search.value !== '') {
        params.search = search.value;
    }
    $.post('/records/search', params).done(async (res) => {
        searchResults.value = res.data.results;
        searchLoading.value = false;
        await nextTick();
        searchRecordsModalRef.value.centerY();
    })
}
const showSearch = () => {
    searchRecordsModalRef.value.show();
    loadSearch();
}
const submitSelectedRecords = async () => {
    searchRecordsModalRef.value.hide();
    if (selectedRecords.value.length === 0) {
        return;
    }
    recordsList.value = [...recordsList.value, ...selectedRecords.value.map(record => {
        return {
            is_annotation: false,
            model: record
        }
    })];
    await nextTick();
    scrollToEnd();
}

const scrollToEnd = async () => {
    itemsRef.value.scrollIntoView({
        block: 'end',
        behavior: 'smooth'
    })
}
</script>
