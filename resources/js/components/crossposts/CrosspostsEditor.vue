<template>
    <div class="crossposts-editor">

        <modal ref="settingsModal" title="Настройки постинга" :loading="settingsPanel.loading" class="modal">
            <div v-if="settings[settingsPanel.networkId]" class="form__content">
                <div class="input-container input-container--vertical">
                    <label class="input-container__label">Текст</label>
                    <div class="input-container__inner">
                        <textarea :maxlength="settingsPanel.networkId === 'twitter' ? 257 : ''" class="input input--textarea" v-model="settings[settingsPanel.networkId].text"></textarea>
                    </div>
                </div>
                <div class="input-container input-container--vertical">
                    <label class="input-container__label">Ссылка</label>
                    <div class="input-container__inner">
                        <input class="input" v-model="settings[settingsPanel.networkId].link"/>
                    </div>
                </div>
                <div class="input-container input-container--vertical">
                    <label class="input-container__label">Картинка</label>
                    <div class="input-container__inner">
                        <PictureUploader :key="'upload_'+settingsPanel.networkId" :light="true" tag="crosspost" :data="settings[settingsPanel.networkId].picture" v-model="settings[settingsPanel.networkId].picture" :returnPath="true" />
                     </div>
                </div>
                <div class="form__bottom">
                    <a @click="settingsModal.hide()" class="button button--light">ОК</a>
                </div>
            </div>

        </modal>

        <div class="crossposts-editor__title">Постинг в соцсети</div>
        <div class="crossposts-editor__networks">
            <div class="crossposts-editor__network" v-for="(network, $index) in networks" :key="$index">
                <Preloader v-if="statusesByNetwork[network.id] === STATUS_LOADING" />
                <span class="crossposts-editor__network__name">{{network.name}}</span>
                <span class="crossposts-editor__network__status" :class="'crossposts-editor__network__status--'+statusClasses[statusesByNetwork[network.id]]">Статус: <strong>{{statusesByNetwork[network.id] === STATUS_SUCCESS ? "Готово" : (statusesByNetwork[network.id] === STATUS_NONE ? "Не готово" : errorsByNetwork[network.id] ) }}</strong></span>
                <a class="crossposts-editor__network__link" v-if="crosspostsByNetwork[network.id] && statusesByNetwork[network.id] === 1" :href="crosspostsByNetwork[network.id].link" target="_blank">Просмотреть пост</a>
                <div class="crossposts-editor__network__buttons">
                    <a class="button button--light" @click="showSettings(network.id)">Настройки</a>
                    <a class="button button--light" @click="makePost(network.id)">
                        {{statusesByNetwork[network.id] === STATUS_SUCCESS ? "Обновить пост" : "Сделать пост" }}
                        <span class="tooltip" v-if="!network.can_edit_posts && statusesByNetwork[network.id] === 1">В данной соцсети нельзя редактировать посты, поэтому пост будет удален и сделан снова</span>
                    </a>
                    <a v-if="statusesByNetwork[network.id] === STATUS_SUCCESS" class="button button--light" @click="deletePost(network.id)">Удалить пост</a>
                </div>
            </div>
        </div>
    </div>
</template>
<style lang="scss">
    .crossposts-editor {
        background: var(--bg-darker);
        border-radius: var(--border-radius-standard);
        width: 100%;
        box-shadow: var(--block-box-shadow);
        margin-top: 2em;
        &__title {
            font-size: 1.25em;
            font-weight: bold;
            padding: .75em;
        }

        &__network {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            padding: .5em .85em;
            border-top: 1px solid #ccc;
            &__status {
                &--error {
                    strong {
                        color: #f00;
                    }
                }
                &--success {
                    strong {
                        color: blue;
                    }
                }
            }
            &__name {
                font-size: 1.125em;
                font-weight: 500;
                margin: 0 .5em 0 0;
            }

            &__link {
                margin: 0 1em;
            }
            &__buttons {
                white-space: nowrap;
                flex: 1;
                text-align: right;
            }
        }
    }
</style>
<script setup lang="ts">
    import { ref, reactive, onMounted } from 'vue';
    import PictureUploader from '../PictureUploader.vue';
    import Modal from '../Modal.vue';
    import Preloader from "../Preloader.vue";
    import { getErrorMessage } from "@/utils/errors";

    const STATUS_LOADING = -2;
    const STATUS_NONE = -1;
    const STATUS_ERROR = 0;
    const STATUS_SUCCESS = 1;

    interface CrosspostNetwork {
        id: string,
        name: string,
        can_edit_posts: boolean
    }

    const props = defineProps<{
        crossposts: Models.Crosspost[],
        article: Models.Article,
        networks: CrosspostNetwork[]
    }>();

    const settingsModal = ref(null);

    const settingsPanel = reactive({
        response: null,
        loading: false,
        networkId: null
    });

    const settings = reactive({});
    const statusClasses = {
        '0': 'error',
        '1': 'success'
    };
    const errorsByNetwork = reactive({});
    const crosspostsByNetwork = reactive({});
    const statusesByNetwork = reactive({});
    const networksById = reactive({});

    const showSettings = (networkId) => {
        settingsPanel.networkId = networkId;
        if (!settings[networkId]) {
            settingsPanel.loading = true;
            settings[networkId] = {
                text: '',
                link: '',
                picture: ''
            };
            settingsModal.value.show();
            $.get(route('articles.get-crosspost-parameters'), {article_id: props.article.id, service_id: networkId}).done((res) => {
                settingsPanel.loading = false;
                settings[networkId] = res.data;
            })
        } else {
            settingsModal.value.show();
        }
    };

    const deletePost = (networkId) => {
        return new Promise<void>((resolve, reject) => {
            statusesByNetwork[networkId] = -2;
            $.post(route('articles.crosspost'), {article_id: props.article.id, service_id: networkId, delete: true}).done((res) => {
                if (res.status) {
                    statusesByNetwork[networkId] = -1;
                    resolve();
                } else {
                    errorsByNetwork[networkId] = res.text;
                    statusesByNetwork[networkId] = STATUS_ERROR;
                    reject(res.text);
                }
            }).catch(err => {
                errorsByNetwork[networkId] = getErrorMessage(err);
                statusesByNetwork[networkId] = STATUS_ERROR;
                reject(errorsByNetwork[networkId]);
            })
        })
    };

    const makePost = async (networkId) => {
        if (statusesByNetwork[networkId] === STATUS_SUCCESS && !networksById[networkId].can_edit_posts) {
            try {
                await deletePost(networkId);
            } catch (e) { }
        }
        statusesByNetwork[networkId] = STATUS_LOADING;

        let data = {article_id: props.article.id, network_id: networkId};
        if (settings[networkId]) {
            data = {...data, ...settings[networkId]};
        }
        $.post(route('articles.crosspost'), data).done((res) => {
            if (res.status) {
                statusesByNetwork[networkId] = 1;
                const crosspost = res.data.crosspost;
                crosspost.link = res.data.link;
                crosspostsByNetwork[networkId] = crosspost;
            } else {
                errorsByNetwork[networkId] = res.text;
                statusesByNetwork[networkId] = 0;
            }
        }).catch(err => {
            errorsByNetwork[networkId] = getErrorMessage(err)
            statusesByNetwork[networkId] = 0;
        })
    };

    onMounted(() => {
        props.crossposts.forEach(crosspost => {
            crosspostsByNetwork[crosspost.network] = crosspost;
        });
        props.networks.forEach(network => {
            statusesByNetwork[network.id] = crosspostsByNetwork[network.id] ? STATUS_SUCCESS : STATUS_NONE;
            networksById[network.id] = network;
        });
    });
</script>
