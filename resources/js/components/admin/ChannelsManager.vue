<template>
    <div class="channels-manager">

        <snackbar ref="snackbar"></snackbar>

        <modal ref="logoModal" title="Загрузка по URL" :loading="logoPanel.loading" class="modal">
            <div class="input-container input-container--vertical">
                <label class="input-container__label">Введите URL</label>
                <div class="input-container__inner">
                    <input class="input" v-model="logoPanel.data.address"/>
                </div>
            </div>
            <div class="form__bottom">
                <a @click="loadLogo()" class="button button--light">Загрузить</a>
                <Response :light="true" :data="logoPanel.response"/>
            </div>
        </modal>

        <modal title="Удаление канала" :loading="deletePanel.loading" ref="deleteModal">
            <div class="modal-window__text">Вы уверены, что хотите удалить канал?</div>
            <div class="form__bottom">
                <button class="button button--light" @click="deleteChannel()">ОК</button>
                <button class="button button--light" @click="$refs.deleteModal.hide()">Отмена</button>
                <response :light="true" :data="deletePanel.response"/>
            </div>
        </modal>

        <modal :loading="mergePanel.loading" title="Объединить канал с..." ref="mergeModal">
            <div class="input-container" v-if="!mergePanel.data.is_advertising">
                <label class="input-container__label">Канал</label>
                <div class="input-container__inner">
                    <select2 theme="default" :options="mergeOptions" v-model="mergePanel.data.merged_id"/>
                </div>
            </div>
            <label class="input-container input-container--checkbox">
                <input type="checkbox" v-model="mergePanel.data.is_advertising">
                <div class="input-container--checkbox__element"></div>
                <div class="input-container__label">Переместить в раздел с рекламой?</div>
            </label>
            <div class="form__bottom">
                <button class="button button--light" @click="mergeChannels()">ОК</button>
                <button class="button button--light" @click="$refs.mergeModal.hide()">Отмена</button>
                <response :light="true" :data="mergePanel.response"/>
            </div>
        </modal>
        <div class="admin-panel__heading-container">
            <div class="admin-panel__heading">Управление каналами</div>
        </div>
        <div class="admin-panel__main-content">
            <div class="form__preloader" v-if="table.loading">
                <img src="/pictures/ajax.gif">
            </div>
            <div class="admin-panel__table-filters">
                <div class="pager-container pager-container--light pager-container--admin-panel">
                    <b-pagination v-model="table.currentPage" :total-rows="channelsList.length" :per-page="table.perPage" align="fill" size="sm" class="my-0"></b-pagination>
                </div>
                <div class="admin-panel__table-filters__input">
                    <input class="input" placeholder="Поиск" v-model="table.filter"/>
                </div>
            </div>
            <b-table class="admin-panel__table" show-empty stacked="md" :filter="table.filter" :items="channelsList" :fields="table.fields" :current-page="table.currentPage" :per-page="table.perPage">
                <template v-slot:cell(name)="data">
                    <div class="channels-manager__first-col">
                        <div class="admin-panel__table__row-loading" v-if="data.item._loading"></div>
                        <div class="channels-manager__logo" v-if="data.item.logo" :style="{backgroundImage: 'url('+data.item.logo.url+')'}"></div>
                        <input @change="setNeedSave(channelsList[data.item._index])" class="input" v-model="channelsList[data.item._index].name"/>
                        <a title="Перейти на страницу канала" target="_blank" :href="'/channels/' + data.item.full_url">
                            <i class="fa fa-external-link-square-alt"></i>
                        </a>
                        <span class="channels-manager__not-saved" title="Есть несохраненные изменения" v-if="channelsList[data.item._index]._need_save">*</span>
                    </div>
                </template>
                <template v-slot:cell(is_radio)="data">
                    <input @change="setNeedSave(channelsList[data.item._index])" type="checkbox" v-model="channelsList[data.item._index].is_radio"/>
                </template>
                <template v-slot:cell(is_federal)="data">
                    <input @change="setNeedSave(channelsList[data.item._index])" type="checkbox" v-model="channelsList[data.item._index].is_federal"/>
                </template>
                <template v-slot:cell(is_regional)="data">
                    <input @change="setNeedSave(channelsList[data.item._index])" type="checkbox" v-model="channelsList[data.item._index].is_regional"/>
                </template>
                <template v-slot:cell(city)="data">
                    <input @change="setNeedSave(channelsList[data.item._index])" class="input" v-model="channelsList[data.item._index].city"/>
                </template>
                <template v-slot:cell(is_abroad)="data">
                    <input @change="setNeedSave(channelsList[data.item._index])" type="checkbox" v-model="channelsList[data.item._index].is_abroad"/>
                </template>
                <template v-slot:cell(country)="data">
                    <input @change="setNeedSave(channelsList[data.item._index])" class="input" v-model="channelsList[data.item._index].country"/>
                </template>
                <template v-slot:cell(_options)="data">
                    <div class="channels-manager__buttons">
                        <a @click="showLogoModal(data.item)" class="button button--light">Логотип...</a>
                        <a @click="showMergeModal(data.item)" class="button button--light">Объединить...</a>
                        <a @click="showDeleteModal(data.item)" class="button button--light">Удалить</a>
                    </div>
                </template>
            </b-table>
            <div class="form__bottom form__bottom--admin-panel">
                <a @click="saveChannels()" class="button button--light">Сохранить</a>
                <response :light="true" :data="table.response"/>
            </div>
        </div>
    </div>
</template>
<style lang="scss">
    .channels-manager {
        &__not-saved {
            color: #f00;
            margin: .35em 0 0 .5em;
            font-size: 1.25em;
        }
        &__logo {
            width: 2em;
            height: 2em;
            margin-right: .5em;
            background-size: contain;
            background-position: center center;
            background-repeat: no-repeat;
        }
        &__buttons {
            font-size: .875em;
        }
        &__first-col {
            padding: .25em;
            display: flex;
            align-items: center;
            i {
                margin-left: .5em;
            }
        }
    }
</style>
<script>
    import PictureUploader from '../PictureUploader';
    import Modal from '../Modal';
    import Response from '../Response';
    import Snackbar from '../Snackbar';

    export default {
        computed: {
            mergeOptions() {
                let channels = this.mergePanel.channel ? this.channelsList.filter(channel => channel.id !== this.mergePanel.channel.id) : this.channelsList;
                return channels.map(channel => {
                    return {id: channel.id, text: channel.name};
                })
            }
        },
        methods: {
            setNeedSave(channel) {
                this.$set(channel, '_need_save', true);
            },
            async saveChannelPromise(data) {
                return new Promise((resolve) => {
                    $.post('/channels/' + data.id + '/edit', data).done(res => {
                        resolve(res);
                    }).fail((xhr) => {
                        let error = xhr.responseJSON;
                        resolve({status: 0, text: error.message === "" ? "Неизвестная ошибка" : error.message});
                    })
                })
            },
            async saveChannels() {
                let channels = this.channelsList.filter(channel => channel._need_save);
                this.table.loading = true;
                let hasErrors = false;
                let lastResponse = null;
                for (let index in channels) {
                    if (!hasErrors) {
                        let channel = channels[index];
                        let response = await this.saveChannelPromise(channel);
                        if (response.status) {
                            channel._need_save = false;
                            lastResponse = response;
                        } else {
                            this.table.response = response;
                            hasErrors = true;
                        }
                    }
                }
                if (lastResponse && !hasErrors) {
                    this.table.response = lastResponse;
                }
                this.table.loading = false;
            },
            loadLogo() {
                this.logoPanel.loading = true;
                let data = {
                    url: this.logoPanel.data.address,
                    channel_id: this.logoPanel.channel.id,
                    tag: 'logo'
                };
                $.post('/upload/pictures/by-url', data) .done((res) => {
                    if (res.status) {
                        let pictureData = res.data.picture;
                        $.post('/channels/' + this.logoPanel.channel.id + '/edit', {
                            logo_id: pictureData.id
                        }).done(res => {
                            this.logoPanel.loading = false;
                            this.logoPanel.response = res;
                            if (res.status) {
                               this.$refs.logoModal.hide();
                               this.logoPanel.channel.logo = pictureData;
                            }
                        }).fail((xhr) => {
                            this.logoPanel.loading = false;
                            let error = xhr.responseJSON;
                            this.logoPanel.response = {status: 0, text: error.message === "" ? "Неизвестная ошибка" : error.message};
                        })
                    }
                }).fail((xhr) => {
                    this.logoPanel.loading = false;
                    let error = xhr.responseJSON;
                    this.logoPanel.response = {status: 0, text: error.message === "" ? "Неизвестная ошибка" : error.message};
                })
            },
            showLogoModal(channel) {
                this.logoPanel.response = null;
                this.logoPanel.channel = channel;
                this.$refs.logoModal.show();
            },
            saveChannel(channel) {
                let data = JSON.parse(JSON.stringify(channel));
                this.$set(channel, '_loading', true);
                $.post('/channels/' + data.id + '/edit', data).done(res => {
                    this.$set(channel, '_loading', false);
                    if (res.status) {

                    } else {
                        this.$refs.snackbar.show(res);
                    }
                }).fail((xhr) => {
                    this.$set(channel, '_loading', false);
                    let error = xhr.responseJSON;
                    this.$refs.snackbar.show({status: 0, text: error.message === "" ? "Неизвестная ошибка" : error.message});
                })
            },
            deleteChannel() {
                this.deletePanel.loading = true;
                $.post('/channels/delete', {
                    channel_id: this.deletePanel.channel.id
                }).done(res => {
                    this.deletePanel.loading = false;
                    if (res.status) {
                        this.channelsList = this.channelsList.filter(channel => channel.id !== this.deletePanel.channel.id);
                        this.$refs.deleteModal.hide();
                    }
                }).fail((xhr) => {
                    this.deletePanel.loading = false;
                    let error = xhr.responseJSON;
                    this.deletePanel.response = {status: 0, text: error.message === "" ? "Неизвестная ошибка" : error.message};
                })
            },
            mergeChannels() {
                this.mergePanel.loading = true;
                $.post('/channels/merge', {
                    original_id: this.mergePanel.channel.id,
                    merged_id: this.mergePanel.data.merged_id,
                    is_advertising: this.mergePanel.data.is_advertising
                }).done(res => {
                    this.mergePanel.loading = false;
                    this.mergePanel.response = res;
                    if (res.status) {
                        this.$refs.mergeModal.hide();
                        this.channelsList = this.channelsList.filter(channel => channel.id !== this.mergePanel.channel.id);
                    }
                }).fail((xhr) => {
                    this.mergePanel.loading = false;
                    let error = xhr.responseJSON;
                    this.mergePanel.response = {status: 0, text: error.message === "" ? "Неизвестная ошибка" : error.message};
                })
            },
            showDeleteModal(channel) {
                this.deletePanel.response = null;
                this.deletePanel.channel = channel;
                this.$refs.deleteModal.show();
            },
            showMergeModal(channel) {
                this.mergePanel.response = null;
                this.mergePanel.channel = channel;
                this.$refs.mergeModal.show();
            },
        },
        props: {
            channels: {
                type: Array,
                required: true,
            },
        },
        data() {
            return {
                table: {
                    response: null,
                    loading: false,
                    filter: '',
                    currentPage: 1,
                    perPage: 20,
                    fields: [
                        {
                            key: 'name',
                            label: 'Название',
                            sortable: true
                        },
                        {
                            key: 'is_federal',
                            label: 'Федеральный?',
                            sortable: true
                        },
                        {
                            key: 'is_regional',
                            label: 'Региональный?',
                            sortable: true
                        },
                        {
                            key: 'city',
                            label: 'Город',
                            sortable: true
                        },
                        {
                            key: 'is_abroad',
                            label: 'Зарубежный?',
                            sortable: true
                        },
                        {
                            key: 'country',
                            label: 'Страна',
                            sortable: true
                        },
                        {
                            key: 'is_radio',
                            label: 'Радио?',
                            sortable: true
                        },
                        {
                            key: '_options',
                            label: '',
                            sortable: false
                        },
                    ],
                },
                logoPanel: {
                    data: {
                        address: ''
                    },
                    loading: false,
                    channel: null,
                    response: null
                },
                channelsList: [],
                mergePanel: {
                    data: {
                        merged_id: null,
                        is_advertising: false
                    },
                    loading: false,
                    channel: null,
                    response: null
                },
                deletePanel: {
                    loading: false,
                    channel: null,
                    response: null
                }
            }
        },
        mounted() {
            this.channelsList = this.channels.map((channel, index) => {
                channel._index = index;
                return channel;
            })
        },
        components: {
            Snackbar,
            Response,
            Modal,
            PictureUploader,
        }
    }
</script>
