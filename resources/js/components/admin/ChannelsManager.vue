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
                <button class="button button--light" @click="deleteModalRef?.hide()">Отмена</button>
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
                <button class="button button--light" @click="mergeModalRef?.hide()">Отмена</button>
                <response :light="true" :data="mergePanel.response"/>
            </div>
        </modal>
        <div class="admin-panel__heading-container">
            <div class="admin-panel__heading">Управление каналами</div>
        </div>
        <div class="admin-panel__main-content">
            <div class="form__preloader" v-if="table.loading">
                <img src="/resources/images/ajax.gif">
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
<script setup lang="ts">
import { ref, computed, onMounted, useTemplateRef } from 'vue';
import PictureUploader from '../PictureUploader.vue';
import Modal from '../Modal.vue';
import Response from '../Response.vue';
import Snackbar from '../Snackbar.vue';
import { BTable, BPagination } from 'bootstrap-vue-next'

interface ChannelWithIndex extends Models.Channel {
    _index?: number;
    _need_save?: boolean;
    _loading?: boolean;
}

interface MergeOption {
    id: number;
    text: string;
}

const props = defineProps<{
    channels: Models.Channel[];
}>();

const snackbarRef = useTemplateRef<typeof Snackbar>('snackbar');
const logoModalRef = useTemplateRef<typeof Modal>('logoModal');
const deleteModalRef = useTemplateRef<typeof Modal>('deleteModal');
const mergeModalRef = useTemplateRef<typeof Modal>('mergeModal');

const mergeOptions = computed<MergeOption[]>(() => {
    const channels = mergePanel.value.channel ? channelsList.value.filter(channel => channel.id !== mergePanel.value.channel?.id) : channelsList.value;
    return channels.map(channel => {
        return {id: channel.id, text: channel.name};
    });
});

const table = ref({
    response: null as Forms.Response | null,
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
});

const logoPanel = ref({
    data: {
        address: ''
    },
    loading: false,
    channel: null as Models.Channel | null,
    response: null as Forms.Response | null
});

const channelsList = ref<ChannelWithIndex[]>([]);

const mergePanel = ref({
    data: {
        merged_id: null as number | null,
        is_advertising: false
    },
    loading: false,
    channel: null as Models.Channel | null,
    response: null as Forms.Response | null
});

const deletePanel = ref({
    loading: false,
    channel: null as Models.Channel | null,
    response: null as Forms.Response | null
});

const setNeedSave = (channel: ChannelWithIndex) => {
    channel._need_save = true;
};

const saveChannelPromise = async (data: ChannelWithIndex): Promise<Forms.Response> => {
    return new Promise((resolve) => {
        $.post(route('channels.update', data.id), data).done(res => {
            resolve(res);
        }).fail((xhr) => {
            const error = xhr.responseJSON;
            resolve({status: 0, text: error.message === "" ? "Неизвестная ошибка" : error.message});
        })
    })
};

const saveChannels = async () => {
    const channels = channelsList.value.filter(channel => channel._need_save);
    table.value.loading = true;
    let hasErrors = false;
    let lastResponse: Forms.Response | null = null;
    for (const channel of channels) {
        if (!hasErrors) {
            const response = await saveChannelPromise(channel);
            if (response.status) {
                channel._need_save = false;
                lastResponse = response;
            } else {
                table.value.response = response;
                hasErrors = true;
            }
        }
    }
    if (lastResponse && !hasErrors) {
        table.value.response = lastResponse;
    }
    table.value.loading = false;
};

const loadLogo = () => {
    if (!logoPanel.value.channel) return;
    logoPanel.value.loading = true;
    const data = {
        url: logoPanel.value.data.address,
        channel_id: logoPanel.value.channel.id,
        tag: 'logo'
    };
    $.post(route('pictures.upload'), data).done((res) => {
        if (res.status) {
            const pictureData = res.data.picture;
            $.post(route('channels.update', logoPanel.value.channel!.id), {
                logo_id: pictureData.id
            }).done(res => {
                logoPanel.value.loading = false;
                logoPanel.value.response = res;
                if (res.status) {
                    logoModalRef.value?.hide();
                    if (logoPanel.value.channel) {
                        logoPanel.value.channel.logo = pictureData;
                    }
                }
            }).fail((xhr) => {
                logoPanel.value.loading = false;
                const error = xhr.responseJSON;
                logoPanel.value.response = {status: 0, text: error.message === "" ? "Неизвестная ошибка" : error.message};
            })
        }
    }).fail((xhr) => {
        logoPanel.value.loading = false;
        const error = xhr.responseJSON;
        logoPanel.value.response = {status: 0, text: error.message === "" ? "Неизвестная ошибка" : error.message};
    })
};

const showLogoModal = (channel: Models.Channel) => {
    logoPanel.value.response = null;
    logoPanel.value.channel = channel;
    logoModalRef.value?.show();
};

const deleteChannel = () => {
    if (!deletePanel.value.channel) return;
    deletePanel.value.loading = true;
    $.post(route('channels.delete'), {
        channel_id: deletePanel.value.channel.id
    }).done(res => {
        deletePanel.value.loading = false;
        if (res.status) {
            channelsList.value = channelsList.value.filter(channel => channel.id !== deletePanel.value.channel?.id);
            deleteModalRef.value?.hide();
        }
    }).fail((xhr) => {
        deletePanel.value.loading = false;
        const error = xhr.responseJSON;
        deletePanel.value.response = {status: 0, text: error.message === "" ? "Неизвестная ошибка" : error.message};
    })
};

const mergeChannels = () => {
    if (!mergePanel.value.channel) return;
    mergePanel.value.loading = true;
    $.post(route('channels.merge'), {
        original_id: mergePanel.value.channel.id,
        merged_id: mergePanel.value.data.merged_id,
        is_advertising: mergePanel.value.data.is_advertising
    }).done(res => {
        mergePanel.value.loading = false;
        mergePanel.value.response = res;
        if (res.status) {
            mergeModalRef.value?.hide();
            channelsList.value = channelsList.value.filter(channel => channel.id !== mergePanel.value.channel?.id);
        }
    }).fail((xhr) => {
        mergePanel.value.loading = false;
        const error = xhr.responseJSON;
        mergePanel.value.response = {status: 0, text: error.message === "" ? "Неизвестная ошибка" : error.message};
    })
};

const showDeleteModal = (channel: Models.Channel) => {
    deletePanel.value.response = null;
    deletePanel.value.channel = channel;
    deleteModalRef.value?.show();
};

const showMergeModal = (channel: Models.Channel) => {
    mergePanel.value.response = null;
    mergePanel.value.channel = channel;
    mergeModalRef.value?.show();
};

onMounted(() => {
    channelsList.value = props.channels.map((channel, index) => {
        (channel as ChannelWithIndex)._index = index;
        return channel as ChannelWithIndex;
    });
});
</script>
