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
            <table class="admin-panel__table">
                <thead>
                    <tr>
                        <td>Название</td>
                        <td>Федеральный?</td>
                        <td>Региональный?</td>
                        <td>Город</td>
                        <td>Зарубежный?</td>
                        <td>Страна</td>
                        <td colspan="3"></td>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(channel, $index) in channelsList" :key="$index">
                        <td>
                            <div class="channels-manager__first-col">
                             <div class="admin-panel__table__row-loading" v-if="channel._loading"></div>
                                <div class="channels-manager__logo" v-if="channel.logo" :style="{backgroundImage: 'url('+channel.logo.url+')'}"></div>
                                <input class="input" v-model="channelsList[$index].name"/>
                                <a target="_blank" :href="'/channels/' + channel.id">(видео)</a>
                            </div>
                        </td>
                        <td>
                            <input type="checkbox" v-model="channelsList[$index].is_federal"/>
                        </td>
                        <td>
                            <input type="checkbox" v-model="channelsList[$index].is_regional"/>
                        </td>
                        <td>
                            <input class="input" v-model="channelsList[$index].city"/>
                        </td>
                        <td>
                            <input type="checkbox" v-model="channelsList[$index].is_abroad"/>
                        </td>
                        <td>
                            <input class="input" v-model="channelsList[$index].country"/>
                        </td>
                        <td colspan="3">
                            <div class="channels-manager__buttons">
                                <a @click="showLogoModal(channel)" class="button button--light">Логотип...</a>
                                <a @click="saveChannel(channel)" class="button button--light">Сохранить</a>
                                <a @click="showMergeModal(channel)" class="button button--light">Объединить...</a>
                                <a @click="showDeleteModal(channel)" class="button button--light">Удалить</a>
                            </div>

                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
<style lang="scss">
    .channels-manager {
        &__logo {
            width: 2em;
            height: 2em;
            background-size: contain;
            background-position: center center;
            background-repeat: no-repeat;
        }
        &__buttons {
            font-size: .875em;
        }
        &__first-col {
            display: flex;
            align-items: center;
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
                console.log(this);
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
                logoPanel: {
                    data: {
                        address: ''
                    },
                    loading: false,
                    channel: null,
                    response: null
                },
                channelsList: this.channels,
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

        },
        components: {
            Snackbar,
            Response,
            Modal,
            PictureUploader,
        }
    }
</script>
