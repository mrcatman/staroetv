<template>
    <div class="mass-uploader">
        <input type="file" multiple :accept="isRadio ? 'audio/*' : 'video/*'" ref="fileInputRef"
               @change="onFileInputChange"
               style="display: none"/>
        <preloader v-if="loading"/>
        <div class="form__content">
            <response :data="response"/>
            <div class="mass-uploader__records" v-if="started">
                <mass-uploader-record-form
                    v-for="(record, index) in records"
                    :key="record.title"
                    :record="record"
                    :files="files"
                    :is-radio="isRadio"
                    @remove="() => records.splice(index, 1)"
                />
                <div class="horisontal-delimiter" v-if="records.length"></div>
            </div>
            <template v-if="!started && !isRadio">
                <div class="form__section-label">Загрузка из внешнего источника</div>
                <div class="row row--align-start">
                    <div class="col col--2-5">
                        <input-container vertical label="Ссылка на группу/страницу ВК или канал Youtube">
                            <input class="input" v-model="source"/>
                        </input-container>
                    </div>
                    <div class="col">
                        <div class="input-container input-container--vertical">
                            <input-container vertical label="Код для доступа к странице">
                                <input class="input" v-model="nextPageToken"/>
                                <template #description>Если не указан, будут загружены новейшие видео</template>
                            </input-container>
                        </div>
                    </div>
                </div>
                <button class="button mass-uploader__load-list" :disabled="!source.length" @click="load()">Загрузить
                    список
                </button>
                <div class="horisontal-delimiter"></div>
                <div class="form__section-label">Индивидуальная загрузка</div>
            </template>


            <template v-if="!started || isIndividualUpload">
                <div class="row row--align-start">
                    <div class="col col--2-5" v-if="!isRadio">
                        <input-container vertical label="Ссылка или список ссылок на видео (ВК/Youtube)">
                            <textarea class="input" v-model="links"></textarea>
                        </input-container>
                    </div>
                    <div class="col">
                        <input-container vertical
                                         :label="isRadio ? 'Загрузить с устройства' : 'Или загрузить с устройства'">
                            <div>
                                <button class="button button--big" @click="fileInputRef.click()">Добавить файлы</button>
                            </div>
                        </input-container>
                    </div>
                </div>
                <button v-if="!isRadio" class="button" :disabled="!links.length" @click="loadLinks()">Добавить</button>
            </template>


            <div class="form__bottom" v-if="records.length && !isIndividualUpload && nextPageToken != ''">
                <button class="button" @click="load()">Загрузить еще</button>
                <div class="mass-uploader__next-token">Код для доступа к следующей странице:
                    <strong>{{ nextPageToken }}</strong>
                </div>
            </div>
        </div>
    </div>
</template>
<style lang="scss">
.mass-uploader {
    &__load-list {
        margin-top: -1.25em;
    }

    &__next-token {
        margin: 1em 0;
    }

    &__records {
        width: 100%;
        display: flex;
        flex-direction: column;
        gap: var(--col-margin);
    }

}
</style>
<script lang="ts" setup>
import { ref, useTemplateRef } from "vue";

import Preloader from "@/components/Preloader.vue";
import Response from "@/components/Response.vue";
import MassUploaderRecordForm from "@/components/mass-uploader/MassUploaderRecordForm.vue";
import { useChannelsStore } from "@/stores/channels";
import { getInfo } from "@/utils/records/get-info";
import { useCategoriesStore } from "@/stores/categories";
import InputContainer from "@/components/InputContainer.vue";
import { getErrorMessage } from "@/utils/errors";

defineProps<{
    isRadio: boolean,
}>();

export type MultipleRecordsResponseItem = {
    title: string,
    description?: string,
    duration?: number,
    player?: string,
    code?: string,
    thumbnails?: string[]
    file?: string,
    upload?: File,
}

const loading = ref<boolean>(false);
const response = ref<Forms.Response>();
const started = ref<boolean>(false);
const isIndividualUpload = ref<boolean>(false);
const links = ref<string>('');

const source = ref<string>(localStorage.getItem('mass-uploader-source') ?? '');
const nextPageToken = ref<string>('');

const files = ref<string[]>([]);
const records = ref<MultipleRecordsResponseItem[]>([]);

const load = () => {
    loading.value = true;

    response.value = null;
    $.post(route('mass-upload.list'), {
        source: source.value,
        next_page_token: nextPageToken.value,
    }).then((res) => {
        loading.value = false;
        if (res.status) {
            nextPageToken.value = res.data.next_page_token;

            files.value = res.data.files;
            records.value = [...records.value, ...res.data.items];

            isIndividualUpload.value = false;
            started.value = true;

            localStorage.setItem('mass-uploader-source', source.value);
        } else {
            response.value = res;
        }
    }).catch((e: JQuery.jqXHR) => {
        response.value = {
            status: 0,
            text: getErrorMessage(e)
        };
        loading.value = false;
    });
}

const loadLinks = async () => {
    const _links = links.value;
    links.value = '';
    loading.value = true;
    isIndividualUpload.value = true;

    for (const link of (_links.match(/(https?:\/\/[^\s]+)/g) ?? [])) {
        try {
            const {
                id, title, description, player, code, thumbnails
            } = await getInfo(link);
            if (!id) {
                return;
            }

            records.value.push({
                title,
                description,
                player,
                code,
                thumbnails,
            })
            started.value = true;
        } catch (e) {

        }
    }
    loading.value = false;
}

const channelsStore = useChannelsStore();
channelsStore.load();

const fileInputRef = useTemplateRef<HTMLInputElement>('fileInputRef');
const onFileInputChange = () => {
    Array.from(fileInputRef.value.files).forEach(file => {
        records.value.push({
            title: file.name.split('.').slice(0, -1).join('.'),
            upload: file,
        })
    });
    isIndividualUpload.value = true;
    started.value = true;
}

const categoriesStore = useCategoriesStore();
categoriesStore.load();
</script>
