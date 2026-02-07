<template>
    <div class="form__content crossposts-manager">
        <response :data="response"/>

        <input-container vertical label="Время публикации" label-small>
            <label class="input-container input-container--radio">
                <input type="radio" v-model="publishNow" :value="true">
                <div class="input-container--radio__element"></div>
                <div class="input-container__label">Вручную</div>
            </label>
            <label class="input-container input-container--radio">
                <input type="radio" v-model="publishNow" :value="false">
                <div class="input-container--radio__element"></div>
                <div class="input-container__label">Выбрать время</div>
                <datepicker type="datetime-local" v-if="!publishNow" v-model="postTime"/>
            </label>
        </input-container>

        <input-container vertical label="Текст">
            <textarea class="input input--textarea" v-model="data.text"></textarea>
        </input-container>

        <input-container vertical label="Короткий текст (для твиттера)"
                         v-show="dataByService.twitter && dataByService.twitter.active">
            <div class="input-container__element-outer">
                <div v-if="data.short_texts.length > 0" class="input-container__inner"
                     v-for="(text, $index) in data.short_texts" :key="$index">
                    <div class="row">
                        <div class="col">
                            <textarea maxlength="280" class="input" v-model="data.short_texts[$index]"></textarea>
                        </div>
                        <div class="col col--auto">
                            <a @click="data.short_texts.splice($index)" class="button button--light">X</a>
                        </div>
                    </div>
                </div>
                <div class="crossposts-manager__add-text">
                    <a class="button" @click="addNewShortText()">Добавить</a>
                </div>
            </div>
        </input-container>

        <input-container label="Ссылка">
            <input class="input" v-model="data.link"/>
        </input-container>
        <input-container label="Описание ссылки">
            <input class="input" v-model="data.link_text"/>
        </input-container>

        <div class="horisontal-delimiter"></div>

        <input-container vertical label="Медиа" label-small>
            <div class="form__content">
                <div class="row" :key="$index" v-for="(item, $index) in data.media">
                    <div class="col" v-if="item.type === 'video'">
                        <input-container vertical label="Ссылка на видео">
                            <input class="input" v-model="item.value"/>
                        </input-container>
                    </div>

                    <div class="col" v-if="item.type === 'picture'">
                        <input-container vertical label="Картинка">
                            <picture-uploader tag="crosspost" v-model:url="item.value"/>
                        </input-container>
                    </div>

                    <div class="col"
                         v-if="item.type === 'video' && item.value && item.value.length > 0 && item.value.indexOf('youtu') === -1">
                        <input-container vertical label="Альтернативная ссылка (youtube)">
                            <input class="input" v-model="item.value_alt"/>
                        </input-container>
                    </div>

                    <div class="col" v-show="$index > 0">
                        <input-container vertical class="crossposts-manager__media__right" label="Описание">
                            <input class="input" v-model="item.text"/>
                        </input-container>
                    </div>


                    <div class="col col--auto">
                        <a class="button" @click="data.media.splice($index, 1)">Удалить</a>
                    </div>
                </div>
            </div>
            <div class="horisontal-delimiter"></div>
            <div class="buttons-row">
                <a @click="data.media.push({type: 'video', value: null, text: ''})" class="button button--light">Добавить
                    видео</a>
                <a @click="data.media.push({type: 'picture', value: null, text: ''})" class="button button--light">Добавить
                    картинку</a>
            </div>
        </input-container>

        <div class="crossposts-manager__services">
            <div class="crossposts-manager__description">
                Отметьте сервисы, в которые нужно сделать пост, затем нажмите "Сохранить".
                <template v-if="!props.crosspost?.id">
                    Либо сохраните пост и затем нажмите на кнопку "Сделать пост" вручную для нужных сервисов
                </template>
                <template v-else>
                    Либо нажмите на кнопку "Сделать пост" вручную для нужных сервисов
                </template>
            </div>
            <div class="crossposts-manager__service"
                 v-for="service in activeServices" :key="service.id">
                <div class="crossposts-manager__service__texts">
                    <input-container checkbox>
                        <input type="checkbox" v-model="dataByService[service.id].active">
                        <div class="input-container--checkbox__element"></div>
                        <div class="input-container__label">{{ service.name }}</div>
                    </input-container>
                    <div class="crossposts-manager__service__status">
                        Статус:
                        <span v-if="!dataByService[service.id].data || dataByService[service.id].data.status === STATUS_NONE">Не готово</span>
                        <span v-else-if="dataByService[service.id].data && dataByService[service.id].data.status === STATUS_ERROR">Ошибка: <strong
                            class="crossposts-manager__service__status__error">{{
                                dataByService[service.id].data.error_log
                            }}</strong></span>
                        <span v-else-if="dataByService[service.id].data && dataByService[service.id].data.status === STATUS_SUCCESS">Готово</span>
                    </div>
                    <div
                        v-if="dataByService[service.id].data && dataByService[service.id].data.links && dataByService[service.id].data.links.length > 0"
                        class="crossposts-manager__links">
                        Ссылки: <a class="crossposts-manager__link" :href="link" target="_blank"
                                   v-for="(link, $index) in dataByService[service.id].data.links">[{{ $index }}]</a>
                    </div>
                </div>
                <div class="crossposts-manager__service__buttons" v-if="props.crosspost?.id">
                    <div class="buttons-row">
                        <a class="button button--light"
                           v-if="!dataByService[service.id].data || dataByService[service.id].data.status === STATUS_NONE"
                           @click="makePost(service.id)">Сделать пост</a>
                        <a class="button button--light" v-if="dataByService[service.id].data && service.can_edit_posts"
                           @click="makePost(service.id)">Обновить пост</a>
                        <a class="button button--light" v-if="dataByService[service.id].data && !service.can_edit_posts"
                           @click="makePost(service.id, true)">Сделать пост заново</a>
                        <a class="button button--light"
                           v-if="dataByService[service.id].data && service.can_delete_posts && dataByService[service.id].data.post_ids &&  dataByService[service.id].data.post_ids !== ''"
                           @click="deletePost(service.id)">Удалить пост</a>
                    </div>
                </div>
            </div>
        </div>
        <button class="button" @click="save()">Сохранить</button>
    </div>
</template>
<style lang="scss">
.crossposts-manager {
    &__service {
        display: flex;
        align-items: center;
        justify-content: space-between;
        &__status {
            &__error {
                color: #f00;
            }
        }
    }

    &__services {
        width: 100%;
        box-sizing: border-box;
        background: var(--bg-darker);
        padding: 1em;
        border-radius: var(--border-radius-standard);
        box-shadow: var(--block-box-shadow);
        margin-top: 1em;
        display: flex;
        flex-direction: column;
        gap: var(--col-margin);
    }

    &__description {
        font-size: 1.125em;
        font-weight: 600;
    }

    &__media {
        margin: 2em 0;

        &__title {
            font-size: 1.25em;
            font-weight: bold;
            margin: 0 0 .5em;
        }

        &__delete {
            margin: 1em 0 1em 1em;
        }

    }
}
</style>
<script lang="ts" setup>
import { computed, ref, watch } from "vue";
import PictureUploader from '../PictureUploader.vue';
import Response from '../Response.vue';
import Datepicker from '../Datepicker.vue'
import InputContainer from "../InputContainer.vue";

interface CrosspostService {
    id: string,
    name: string,
    is_active: boolean,
    can_auto_connect: boolean,
    can_edit_posts: boolean,
    can_delete_posts: boolean,
    settings: any
}
const STATUS_NONE = -1;
const STATUS_ERROR = 0;
const STATUS_SUCCESS = 1;

const props = defineProps<{
    services: CrosspostService[],
    crosspost?: Models.SocialPost,
    id?: number,
}>();
const postTime = ref(null);
const publishNow = ref(props.crosspost ? props.crosspost.post_ts === 0 : true);

watch(() => publishNow.value, () => {
    postTime.value = null;
})

const response = ref<Forms.Response>(null);
const data = ref<Models.SocialPost>({
    ...props.crosspost?.post_data || {
        short_texts: [''],
        media: []
    },
});

type dataByService = {
    active: boolean,
    data?: Models.SocialPostConnection,
    [key: string]: any
}
const dataByService = ref<{
    [key: string]: dataByService
}>({});

const addNewShortText = () => {
    data.value.short_texts.push('');
}

const hasConnections = !!props.crosspost?.post_connections;
props.services.forEach(service => {
    dataByService.value[service.id] = {
        active: hasConnections ? false : service.is_active
    }
});

const activeServices = computed(() => {
    return props.services.filter(service => {
        return dataByService.value[service.id]?.active
    });
});

props.crosspost?.post_connections.forEach(postConnection => {
    dataByService.value[postConnection.service] = {
        active: true,
        data: postConnection
    }
})

const makePost = (service: string, force = false) => {
    $.post(route('crossposts.make-post', {id: props.crosspost.id, service, force})).done(res => {
        response.value = res;
        dataByService.value[service].data.status = STATUS_SUCCESS;
        dataByService.value[service].data = res.data.post_connection;
    }).catch((e) => {
        if (!dataByService.value[service].data) {
            dataByService.value[service].data = {};
        }
        dataByService.value[service].data.status = STATUS_ERROR;
        dataByService.value[service].data.error_log = e.responseJSON?.message ?? 'Неизвестная ошибка'
    })
}

const deletePost = (service: string) => {
    $.post(route('crossposts.delete-post', {id: props.crosspost.id, service})).done(res => {
        response.value = res;
        dataByService.value[service].data = null;
    })
}

const save = () => {
    const servicesList = [];
    props.services.forEach(service => {
        if (dataByService.value[service.id].active) {
            servicesList.push(service.id);
        }
    });
    const url = props.crosspost?.id ? route('crossposts.edit', props.crosspost.id) : route('crossposts.add');
    $.post(url, {
        post_time: postTime.value,
        data: data.value,
        services: servicesList
    }).done(res => {
        window.scrollTo(0, 0);
        response.value = res;
        if (res.status) {
            if (!props.crosspost.id) {
                window.location.href = res.redirect_to;
            }
        }
    }).fail((xhr) => {
        window.scrollTo(0, 0);
        response.value = {
            status: 0,
            text: xhr.responseJSON?.message ?? 'Неизвестная ошибка'
        }
    });
}


</script>
