<template>
    <div>
        <snackbar ref="snackbar"></snackbar>
        <input type="hidden" v-if="name" :name="name" :value="dataList">
        <modal ref="searchRecordsModal" title="Поиск записей" :loading="searchPanel.loading">
            <form class="records-list-picker__search">
                <div class="input-container records-list-picker__search__input">
                    <label class="input-container__label">Поиск</label>
                    <div class="input-container__inner">
                        <input @keyup.stop = "() => {}" @keydown.stop = "() => {}"  @keypress.stop = "() => {}"  class="input" v-model="searchPanel.search"/>
                    </div>
                    <input type="text" hidden style="display:none;">
                </div>
                <div class="records-list records-list-picker__search__list" v-if="searchPanel.list && searchPanel.list.data" >
                    <div class="record-item" @click="onSelectRecord(record)" :class="{'record-item--selected': selectedIds.indexOf(record.id) !== -1}" v-for="(record, $index) in searchPanel.list.data">
                        <a target="_blank"  :href="'/video/'+record.id" class="record-item__cover" :style="{backgroundImage: `url(${(record.cover_picture ? record.cover_picture.url : record.cover)})`}"></a :href="'/records/'+record.id">
                        <div class="record-item__texts">
                        <span class="record-item__title" v-html="record.title"></span>
                        </div>
                    </div>
                </div>
                <div class="modal-window__pager records-list-picker__search__pager">
                    <div class="pager-container">
                        <pagination :limit="3" :data="searchPanel.list" @pagination-change-page="loadSearch"></pagination>
                    </div>
                </div>
                <div class="form__bottom records-list-picker__search__submit">
                    <a @click="submitSelectedRecords()" class="button button--light">Выбрать</a>
                    <Response :light="true" :data="searchPanel.response"/>
                </div>
            </form>
        </modal>

        <modal ref="addRecordModal" title="Добавить новую запись">
            <record-form @save="onNewRecord" class="records-list-picker__form" :inModal="true" :meta="meta" :params="params"></record-form>
        </modal>

        <div class="records-list-picker box box--dark">
            <div class="box__heading">
                Выбор записей
                <div class="box__heading__right">
                    <div class="buttons-row">
                        <a class="button" @click="showSearchPanel()">Выбрать с сайта</a>
                        <a class="button" @click="showAddPanel()">Загрузить новое видео</a>
                        <a class="button button--light" v-if="annotations" @click="addAnnotation()">Добавить аннотацию</a>
                    </div>
                </div>
            </div>
            <!--
            <select class="select-classic records-list-picker__type-select" v-model="showRecords" v-if="!hideSelectedButton">
                <option value="all">Все видео</option>
                <option value="main">Основной список</option>
                <option value="other">Остальные</option>
            </select>
            -->
            <div class="box__inner records-list-picker__items">

                <div class="records-list__empty" v-if="recordsList.length === 0">Нет записей</div>
                <component :is="disableDrag ? 'div' : 'draggable'" @change="onDragChange" v-model="recordsList" class="records-list">
                    <div v-for="(record, $index) in recordsList" :key="$index">
                        <div v-if="!record.is_annotation" v-show="showRecords === 'all' || (showRecords === 'main' && record.is_selected) || (showRecords === 'other' &&!record.is_selected)" class="record-item records-list-picker__item" :class="{'records-list-picker__item--updating': record.updating, 'records-list-picker__item--selected': record.is_selected}">
                            <div class="records-list-picker__buttons">
                                <!--
                                <a class="records-list-picker__button" v-if="!hideSelectedButton" @click="setSelected(record)">{{record.is_selected ? "Удалить из осн.списка" : "Добавить в осн.список"}}</a>
                                -->
                                <a class="records-list-picker__button" @click="deleteFromList(record)">Удалить</a>
                                <!--<a class="records-list-picker__delete__button" >Удалить с сайта</a>-->
                            </div>
                            <a target="_blank" :href="'/video/'+record.id" class="record-item__cover" :style="{backgroundImage: `url(${record.cover})`}"></a>
                            <div class="record-item__texts">
                                <span class="record-item__title" v-html="record.title"></span>
                                <div class="records-list-picker__fields-container">
                                    <textarea v-if="descriptions" v-model="record.block_description" class="input" placeholder="Описание"></textarea>
                                    <select @change="updateRecord(record, 'interprogram_type')" class="select-classic" v-if="interprogramEditor" v-model="record.interprogram_type">
                                        <option v-for="type in interprogramTypes" :value="type.id">{{type.text}}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div v-else class="records-list-picker__item records-list-picker__annotation">
                            <div class="records-list-picker__buttons">
                                <a class="records-list-picker__button" @click="recordsList.splice($index, 1)">Удалить</a>
                            </div>
                            <input v-model="record.title" class="input" placeholder="Заголовок аннотации" />
                            <textarea v-model="record.text" class="input" placeholder="Описание"></textarea>
                        </div>

                    </div>
                </component>
            </div>
        </div>
    </div>
</template>
<style lang="scss">
    .records-list-picker {
        margin: 0 0 1em;
        &__header {

        }
        &__title {
            font-size: 1.5em;
            font-weight: 600;
        }
        &__items {
            margin: 1em 0 0;
           .records-list--thumbs {
                margin: 0;
            }
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
            border-radius: .25em;
            margin: 0 0 .25em;
            display: inline-block;
            &:hover {
                filter: brightness(1.25);
            }
        }
         &__fields-container {
            max-width: calc(100% - 5em);
            margin: 0 .125em;
            .input {
                width: 100%;
            }
             .select-classic {
                 width: 50%;
             }

        }
        &__item {
            &:hover {
                background: none!important;
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
            &__input {
                margin: 0 0 .5em;
                font-size: .875em;
            }
            &__list {
                overflow: auto;
                font-size: .875em;
                .record-item {
                    border-bottom: 1px solid var(--border-color) !important;
                }
                .record-item__title {
                    font-size: 1.5em;
                }
            }
            &__pager {
                font-size: .75em;
            }
        }
    }



</style>
<script>
    import Modal from './Modal';
    import Response from './Response';
    import Snackbar from './Snackbar';
    import draggable from 'vuedraggable';
    export default {
        watch: {
            "searchPanel.search"() {
                clearTimeout(this.searchTimeout);
                this.searchTimeout = setTimeout(() => {
                    this.loadSearch(1);
                }, 500)
            }
        },
        computed: {
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
            dataList() {
                return JSON.stringify(this.recordsList.map(record => {
                    return record.is_annotation ? record : {
                        is_annotation: false,
                        id: record.id,
                    }
                }))
            },
            selectedIds() {
                return this.selectedRecords.map(record => record.id);
            }
        },
        methods: {
            addAnnotation() {
                this.recordsList.push({
                    is_annotation: true,
                    title: '',
                    text: ''
                })
            },
            onDragChange() {
                if (this.manual) {
                    this.$emit('selected', this.recordsList);
                }
            },
            onNewRecord(record) {
                this.recordsList.push(record);
                if (this.manual) {
                    this.$emit('selected', this.recordsList);
                }
                this.$refs.addRecordModal.hide();
            },
            showAddPanel() {
                this.$refs.addRecordModal.show();
            },
            updateRecord(record, field) {
                this.$set(record, 'updating', true);
                let params = {};
                params[field] = record[field];
                $.post('/records/mass-edit', {
                    ids: [record.id],
                    params
                }).done(res => {
                    this.$set(record, 'updating', false);
                    if (res.status) {

                    } else {
                        this.$refs.snackbar.show(res);
                    }
                })
            },
            setSelected(record) {
                this.$set(record, 'updating', true);
                $.post('/records/mass-edit', {
                    ids: [record.id],
                    params: {
                        is_selected: !record.is_selected
                    }
                }).done(res => {
                    this.$set(record, 'updating', false);
                    if (res.status) {
                        this.$set(record, 'is_selected', !record.is_selected);
                    } else {
                        this.$refs.snackbar.show(res);
                    }
                })
            },
            deleteFromSite(record) {
                this.$set(record, 'updating', true);
                $.post('/records/delete', {
                    id: record.id,
                }).done(res => {
                    if (res.status) {
                        this.recordsList.splice(this.recordsList.map(item => item.id).indexOf(record.id), 1);
                    } else {
                        this.$set(record, 'updating', false);
                        this.$refs.snackbar.show(res);
                    }
                })
            },
            deleteFromList(record) {
                if (this.manual) {
                    this.recordsList.splice(this.recordsList.map(item => item.id).indexOf(record.id), 1);
                    this.$emit('selected', this.recordsList);
                    return;
                }
                this.$set(record, 'updating', true);
                $.post('/records/mass-edit', {
                    ids: [record.id],
                    params: this.unsetParams
                }).done(res => {
                    if (res.status) {
                        this.recordsList.splice(this.recordsList.map(item => item.id).indexOf(record.id), 1);
                    } else {
                        this.$set(record, 'updating', false);
                        this.$refs.snackbar.show(res);
                    }
                })
            },
            submitSelectedRecords() {
                if (this.manual) {
                    this.recordsList = [...this.recordsList, ...this.selectedRecords];
                    this.$emit('selected', this.recordsList);
                    this.$refs.searchRecordsModal.hide();
                    return;
                }
                this.searchPanel.loading = true;
                $.post('/records/mass-edit', {
                    ids: this.selectedIds,
                    params: this.params,
                }).done(res => {
                    this.searchPanel.response = res;
                    this.searchPanel.loading = false;
                    if (res.status) {
                        this.$refs.searchRecordsModal.hide();
                        this.recordsList = [...this.recordsList, ...this.selectedRecords];
                    }
                })
            },
            onSelectRecord(record) {
                if (this.selectedIds.indexOf(record.id) === -1) {
                    this.selectedRecords.push(record);
                } else {
                    this.selectedRecords.splice(this.selectedIds.indexOf(record.id), 1);
                }
            },
            loadSearch(page) {
                this.searchPanel.loading = true;
                if (page) {
                    this.searchPanel.currentPage = page;
                }

                let params = this.select;
                params.page = this.searchPanel.currentPage;
                params.exclude_ids = this.recordsList.map(record => record.id);
                if (this.searchPanel.search !== '') {
                    params.search = this.searchPanel.search;
                }
                $.post('/records/search', params).done(res => {
                    this.searchPanel.list = res.data.records;
                    this.searchPanel.loading = false;
                    this.$nextTick(() => {
                        //this.$refs.searchRecordsModal.setSize();
                    })
                })
            },
            showSearchPanel() {
                this.$refs.searchRecordsModal.show();
                this.loadSearch();
            },
            loadCategories() {
                $.get('/records/categories').done(res => {
                    this.categories = res.data.categories;
                })
            },
        },
        mounted() {
            this.recordsList = [...this.list, ...(this.annotations ? this.annotations.map(annotation => {
                annotation.is_annotation = true;
                return annotation;
            }) : [])].sort((a,b) => {
                let orderA = a.internal_order ? a.internal_order : a.order;
                let orderB = b.internal_order ? b.internal_order : b.order;
                return orderA - orderB;
            });

            this.loadCategories();
        },
        data() {
            return {
                categories: [],
                showRecords: 'all',
                selectedRecords: [],
                recordsList: [],
                searchPanel: {
                    search: '',
                    currentPage: 1,
                    loading: false,
                    list: {},
                    response: null,
                },
                searchTimeout: null,
            }
        },
        components: {
            Modal, Response, Snackbar, draggable
        },
        props: {
            annotations: {
                type: Array,
                required: false,
            },
            interprogramEditor: {
                type: Boolean,
                required: false,
            },
            descriptions: {
                type: Boolean,
                required: false
            },
            hideSelectedButton: {
                type: Boolean,
                required: false
            },
            name: {
                type: String,
                required: false
            },
            disableDrag: {
                type: Boolean,
                required: false
            },
            manual: {
                type: Boolean,
                required: false
            },
            meta: {
                type: Object,
                required: false
            },
            list: {
                type: Array,
                required: true
            },
            unsetParams: {
                type: Object,
                required: false
            },
            params: {
                type: Object,
                required: false
            },
            select: {
                type: Object,
                required: true
            }
        }
    }
</script>
