<template>
    <div class="crossposts-editor">

        <modal ref="settingsModal" title="Настройки постинга" :loading="settingsPanel.loading" class="modal">
            <div v-if="settings[settingsPanel.networkId]">
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
            </div>
            <div class="form__bottom">
                <a @click="settingsModal.hide()" class="button button--light">ОК</a>
            </div>
        </modal>

        <div class="crossposts-editor__title">Постинг в соцсети</div>
        <div class="crossposts-editor__networks">
            <div class="crossposts-editor__network" v-for="(network, $index) in networks" :key="$index">
                <div class="form__preloader" v-if="statusesByNetwork[network.id] === -2"><img src="/resources/images/ajax.gif"></div>
                <span class="crossposts-editor__network__name">{{network.name}}</span>
                <span class="crossposts-editor__network__status" :class="'crossposts-editor__network__status--'+statusClasses[statusesByNetwork[network.id]]">Статус: <strong>{{statusesByNetwork[network.id] === 1 ? "Готово" : (statusesByNetwork[network.id] === -1 ? "Не готово" : errorsByNetwork[network.id] ) }}</strong></span>
                <a class="crossposts-editor__network__link" v-if="crosspostsByNetwork[network.id] && statusesByNetwork[network.id] === 1" :href="crosspostsByNetwork[network.id].link" target="_blank">Просмотреть пост</a>
                <div class="crossposts-editor__network__buttons">
                    <a class="button button--light" @click="showSettings(network.id)">Настройки</a>
                    <a class="button button--light" @click="makePost(network.id)">
                        {{statusesByNetwork[network.id] === 1 ? "Обновить пост" : "Сделать пост" }}
                        <span class="tooltip" v-if="!network.can_edit_posts && statusesByNetwork[network.id] === 1">В данной соцсети нельзя редактировать посты, поэтому пост будет удален и сделан снова</span>
                    </a>
                    <a v-if="statusesByNetwork[network.id] === 1" class="button button--light" @click="deletePost(network.id)">Удалить пост</a>
                </div>
            </div>
        </div>
    </div>
</template>
<style lang="scss">
    .crossposts-editor {
        background: #efefef;
        color: #555;
        margin: 0 0 1em;
        &__title {
            font-size: 1.125em;
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
<script setup>
    import { ref, reactive, onMounted } from 'vue';
    import PictureUploader from './PictureUploader.vue';
    import Modal from './Modal.vue';
    import Response from './Response.vue';
    import Snackbar from './Snackbar.vue';

    const props = defineProps(['crossposts', 'article', 'networks']);

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
            $.get(route('articles.get-crosspost-parameters'), {article_id: props.article.id, network_id: networkId}).done((res) => {
                settingsPanel.loading = false;
                settings[networkId] = res.data;
            })
        } else {
            settingsModal.value.show();
        }
    };

    const deletePost = (networkId) => {
        return new Promise((resolve, reject) => {
            statusesByNetwork[networkId] = -2;
            $.post(route('articles.crosspost'), {article_id: props.article.id, network_id: networkId, delete: true}).done((res) => {
                if (res.status) {
                    statusesByNetwork[networkId] = -1;
                    resolve();
                } else {
                    errorsByNetwork[networkId] = res.text;
                    statusesByNetwork[networkId] = 0;
                    reject(res.text);
                }
            }).catch(xhr => {
                let error = xhr.responseJSON;
                let text = error.message || "Неизвестная ошибка";
                errorsByNetwork[networkId] = text;
                statusesByNetwork[networkId] = 0;
                reject(text);
            })
        })
    };

    const makePost = async (networkId) => {
        if (statusesByNetwork[networkId] === 1) {
            if (!networksById[networkId].can_edit_posts) {
                await deletePost(networkId);
            }
        }
        statusesByNetwork[networkId] = -2;

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
        }).catch(xhr => {
            errorsByNetwork[networkId] = xhr.responseJSON?.message || "Неизвестная ошибка";
            statusesByNetwork[networkId] = 0;
        })
    };

    onMounted(() => {
        props.crossposts.forEach(crosspost => {
            crosspostsByNetwork[crosspost.network] = crosspost;
        });
        props.networks.forEach(network => {
            statusesByNetwork[network.id] = crosspostsByNetwork[network.id] ? 1 : -1;
            networksById[network.id] = network;
        });
    });
</script>
