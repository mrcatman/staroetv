<template>
    <div>
        <snackbar ref="snackbar"></snackbar>
        <modal ref="searchRecordsModal" title="Поиск записей" :loading="searchPanel.loading">
            <div class="records-list records-list--thumbs" v-if="searchPanel.list && searchPanel.list.data" >
                <div class="record-item" @click="onSelectRecord(record)" :class="{'record-item--selected': selectedIds.indexOf(record.id) !== -1}" v-for="(record, $index) in searchPanel.list.data">
                    <div class="record-item__cover" :style="{backgroundImage: `url(${record.cover})`}"></div>
                        <div class="record-item__texts">
                        <span class="record-item__title">
                           {{record.title}}
                        </span>
                    </div>
                </div>
            </div>
            <div class="modal-window__pager">
                <div class="pager-container--light">
                    <pagination :limit="3" :data="searchPanel.list" @pagination-change-page="loadSearch"></pagination>
                </div>
            </div>
            <div class="form__bottom form__bottom--with-margin">
                <a @click="submitSelectedRecords()" class="button button--light" v-show="selectedIds.length > 0">Выбрать</a>
                <Response :light="true" :data="searchPanel.response"/>
            </div>
        </modal>

        <modal ref="addRecordModal" title="Добавить новую запись">
            <record-form @save="onNewRecord" class="records-list-picker__form" :inModal="true" :meta="meta" :params="params"></record-form>
        </modal>

        <div class="records-list-picker">
            <div class="records-list-picker__header">
                <div class="records-list-picker__title">Выбор записей</div>
                <div class="records-list-picker__buttons">
                    <a class="button button--light" @click="showSearchPanel()">Выбрать из списка</a>
                    <a class="button button--light" @click="showAddPanel()">Загрузить новые</a>
                </div>
            </div>
            <div class="records-list-picker__items">
                <div class="records-list records-list--thumbs">
                    <div class="record-item records-list-picker__item" :class="{'records-list-picker__item--deleting': record.isDeleting}" v-for="(record, $index) in recordsList" :key="$index">
                        <div class="records-list-picker__delete">
                            <a class="records-list-picker__delete__button" @click="deleteFromList(record)">Удалить из списка</a>
                            <a class="records-list-picker__delete__button" @click="deleteFromSite(record)">Удалить с сайта</a>
                        </div>

                        <div class="record-item__cover" :style="{backgroundImage: `url(${record.cover})`}"></div>
                        <div class="record-item__texts">
                            <span class="record-item__title">
                               {{record.title}}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<style lang="scss">
    .records-list-picker {
        margin: 0 0 1em;
        &__header {
            background: linear-gradient(#fefefe, #d5d5d5);
            border-bottom: 1px solid #5f5e5e;
            position: relative;
            box-shadow: 0 0.25em 1em rgba(0, 0, 0, 0.79);
            color: #000;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .5em 1em;
        }
        &__title {
            font-size: 1.5em;
            font-weight: 600;
        }
        &__items {
            background: linear-gradient(0deg, #2b1e13, hsl(37, 6%, 25%));
            box-shadow: 0.5em 0.5em 3em inset #000;
            .records-list--thumbs {
                margin: 0;
            }
        }
        &__delete {
            position: absolute;
            top: .5em;
            right: .75em;
            opacity: 0;
            cursor: pointer;
            text-align: right;
            &__button {
                background: #000;
                color: #c4bd97;
                padding: .25em .5em;
                border-radius: .25em;
                margin: 0 0 .25em;
                display: inline-block;
                &:hover {
                    filter: brightness(1.25);
                }
            }
        }

        &__item {
            &--deleting {
                opacity: .5;
            }
        }
        .record-item:hover &__delete {
            opacity: 1;
        }
        &__form {
            margin: -2em -1em;
            padding: 2em 1em;
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
                height: 4.5em;
                &:hover {
                    border: 2px solid rgba(255, 255, 255, 0);
                    box-shadow: 0 0 .5em;
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
    }

</style>
<script>
    import Modal from './Modal';
    import Response from './Response';
    import Snackbar from './Snackbar';
    export default {
        computed: {
            selectedIds() {
                return this.selectedRecords.map(record => record.id);
            }
        },
        methods: {
            onNewRecord(record) {
                this.recordsList.push(record);
                this.$refs.addRecordModal.hide();
            },
            showAddPanel() {
                this.$refs.addRecordModal.show();
            },
            deleteFromSite(record) {
                this.$set(record, 'isDeleting', true);
                $.post('/records/delete', {
                    id: record.id,
                }).done(res => {
                    if (res.status) {
                        this.recordsList.splice(this.recordsList.map(item => item.id).indexOf(record.id), 1);
                    } else {
                        this.$set(record, 'isDeleting', false);
                        this.$refs.snackbar.show(res);
                    }
                })
            },
            deleteFromList(record) {
                this.$set(record, 'isDeleting', true);
                $.post('/records/mass-edit', {
                    ids: [record.id],
                    params: this.unsetParams
                }).done(res => {
                    if (res.status) {
                        this.recordsList.splice(this.recordsList.map(item => item.id).indexOf(record.id), 1);
                    } else {
                        this.$set(record, 'isDeleting', false);
                        this.$refs.snackbar.show(res);
                    }
                })
            },
            submitSelectedRecords() {
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
            }
        },
        mounted() {

        },
        data() {
            return {
                selectedRecords: [],
                recordsList: this.list,
                searchPanel: {
                    currentPage: 1,
                    loading: false,
                    list: {},
                    response: null,
                }
            }
        },
        components: {
            Modal, Response, Snackbar
        },
        props: {
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
                required: true
            },
            select: {
                type: Object,
                required: true
            }
        }
    }
</script>
