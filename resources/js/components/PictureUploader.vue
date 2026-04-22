<template>
    <div class="picture-uploader" :class="{'picture-uploader--light': light}">

        <modal ref="URLModalElement" title="Загрузка по URL" :loading="URLModal.loading" class="modal">
            <div class="form__content">
                <div class="input-container input-container--vertical">
                    <label class="input-container__label">Введите адрес</label>
                    <div class="input-container__inner">
                        <input class="input" v-model="URLModal.address"/>
                    </div>
                </div>
                <div class="form__bottom">
                    <a @click="loadPictureFromURL()" class="button button--light">Загрузить</a>
                    <Response :light="true" :data="URLModal.response"/>
                </div>
            </div>
        </modal>

        <input type="hidden" :value="picture?.id || null" :name="name"/>
        <div class="picture-uploader__inner">
            <a class="picture-uploader__reset" v-if="picture && picture.url" @click="picture = null">удалить</a>
            <div class="picture-uploader__image" v-if="picture && picture.url"
                 :style="{backgroundImage: `url(${picture.url})`}"></div>
            <div v-else class="picture-uploader__empty">Нет картинки</div>
            <Preloader class="picture-uploader__preloader" v-if="loading"/>
        </div>
        <div class="picture-uploader__buttons">
            <label ref="label" class="button button--light">
                Загрузить
                <input style="display:none" @change="onFileInputChange" type="file"/>
            </label>
            <a class="button button--light" @click="loadFromURL()">URL</a>
            <Response :light="true" v-if="error" :data="{status: 0, text: error}"/>
        </div>
    </div>
</template>
<style lang="scss">
@use "../../sass/mixins" as *;

.picture-uploader {
    display: flex;
    align-items: center;
    padding: 1em;
    background: var(--bg-darker);
    border-radius: var(--border-radius-standard);
    box-shadow: var(--element-box-shadow);

    @include mobile {
        box-sizing: border-box;
        width: 100%;
    }

    &__empty {
        width: 100%;
        height: 100%;
        background: var(--inputs-color);
        color: var(--text-lightest);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    &__reset {
        background: var(--box-color-dark);
        color: var(--box-text-color-dark);
        padding: .25em .5em;
        border-radius: var(--border-radius-small);
        opacity: .35;
        height: 1em;
        position: absolute;
        top: .25em;
        right: .25em;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        text-decoration: none !important;
        line-height: 0;

        &:hover {
            opacity: .75;
        }
    }


    &__image {
        background-color: var(--bg-darker-2);
        width: 100%;
        height: 100%;
        background-size: contain;
        background-position: center center;
        background-repeat: no-repeat;
    }

    &__inner {
        width: 9em;
        height: 9em;
        border: none;
        margin: 0 1em 0 0;
        position: relative;
    }

    &__buttons {
        display: flex;
        flex-direction: column;
        text-align: center;
        gap: .5em;
    }

    &__preloader {

        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    &__list {
        overflow: auto;
        height: 100%;
    }

    &__item {
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1em;
        cursor: pointer;

        &:hover {
            background: rgba(0, 0, 0, 0.025);
        }

        &__image {
            width: 7em;
            height: 4em;
            background-size: contain;
            background-position: center center;
            background-repeat: no-repeat;
        }
    }
}

</style>
<script setup lang="ts">
import { ref, reactive, watch, useTemplateRef } from 'vue';
import Modal from './Modal.vue';
import Response from './Response.vue';
import Preloader from "./Preloader.vue";
import { getErrorMessage } from "@/utils/errors";

const extensions = ['png', 'jpg', 'jpeg', 'gif', 'svg', 'webp'];

const props = defineProps<{
    light?: boolean;
    tag?: string;
    name?: string;
    channelId?: number;
    data?: Models.Picture
}>();
const emit = defineEmits<{
    (e: 'change', picture: Models.Picture)
}>();

const picture = defineModel<Models.Picture>();
const url = defineModel<string>('path');

if (!picture.value) {
    picture.value = props.data;
}
if (!url.value) {
    url.value = props.data?.url || '';
}

interface URLModalState {
    address: string;
    visible: boolean;
    loading: boolean;
    response: any;
}

const URLModalElement = useTemplateRef<typeof Modal>('URLModalElement');
const URLModal = reactive<URLModalState>({
    address: '',
    visible: false,
    loading: false,
    response: null
});

const loading = ref<boolean>(false);
const error = ref<string>('');

const loadPictureFromURL = () => {
    URLModal.loading = true;
    const data: any = {
        url: URLModal.address
    };

    if (props.channelId) {
        data.channel_id = props.channelId;
    }
    if (props.tag) {
        data.tag = props.tag;
    }
    $.post(route('pictures.upload-by-url'), data).done((res: any) => {
        URLModal.loading = false;
        URLModal.response = res;
        if (res.status) {
            picture.value = res.data.picture;
            URLModalElement.value?.hide();
        }
    }).fail((e: any) => {
        URLModal.loading = false;
        URLModal.response = {status: 0, text: e.responseJSON.message || 'Ошибка загрузки, попробуйте позже'};
    });
};


const onFileInputChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    const image = target.files[0];
    if (!image) {
        return;
    }

    const ext = /(?:\.([^.]+))?$/.exec(image.name)[1]?.toLowerCase();
    if (extensions.indexOf(ext) !== -1) {
        const fd = new FormData();
        if (props.channelId) {
            // @ts-ignore
            fd.append('channel_id', props.channelId);
        }
        if (props.tag) {
            fd.append('tag', props.tag);
        }

        fd.append('picture', image);

        loading.value = true;
        error.value = '';

        $.ajax({
            url: route('pictures.upload'),
            data: fd,
            processData: false,
            contentType: false,
            type: 'POST',
            success: (data: any) => {
                if (data.status) {
                    picture.value = data.data.picture;
                } else {
                    error.value = data.text;
                }
                loading.value = false;
            },
            error: (e: any) => {
                error.value = getErrorMessage(e);
                loading.value = false;
            }
        });
    } else {
        error.value = 'Неверный формат файла';
    }
}

watch(() => picture.value, () => {
    emit('change', picture.value);
    url.value = picture.value?.url || '';
});

const labelRef = useTemplateRef<HTMLLabelElement>('label');
const loadFile = () => {
    labelRef.value?.click();
}
const loadFromURL = () => {
    URLModalElement.value?.show()
}

defineExpose({
    loadFile,
    loadFromURL,
    error
});

</script>
