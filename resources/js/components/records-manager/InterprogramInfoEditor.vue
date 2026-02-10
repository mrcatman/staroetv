<template>

    <input-container
        vertical
        with-button
    >
        <input class="input" :placeholder="autoUpdateTitle ? 'Заголовок сгенерируется автоматически' : 'Заголовок'" readonly v-model="data.title"/>
        <a @click="updateTitle()" v-if="!autoUpdateTitle"
           class="input-container__button">
            <span class="tooltip">Обновить заголовок</span>
            <i class="fa fa-sync"></i>
        </a>
    </input-container>

    <input-container vertical label="Тип">
        <select2
            theme="default"
            :options="categoriesStore.interprogramTypes"
            v-model="data.interprogram_type"
        />
    </input-container>
    <div class="row">
        <div class="col">
            <input-container vertical label="Пакет оформления">
                <select2
                    theme="default"
                    :options="designPackages"
                    v-model="data.interprogram_package_id"
                />
            </input-container>
        </div>
        <div class="col">
            <input-container
                vertical
                label="Вариация / доп. информация"
            >
                <input class="input" v-model="data.short_description"/>
            </input-container>
        </div>
    </div>



    <div class="horisontal-delimiter"></div>
    <input-container
        vertical
        label="Описание"
    >
        <textarea class="input input--textarea" v-model="data.description"/>
    </input-container>

</template>
<script setup lang="ts">
import { computed, ref, watch } from "vue";
import { useCategoriesStore } from "@/stores/categories";
import { useChannelsStore } from "@/stores/channels";
import { useDesignPackagesStore } from "@/stores/design-packages";

import InputContainer from "@/components/InputContainer.vue";

import { generateInterprogramTitle } from "@/utils/records/generate-title";
import { getNameByDate } from "@/utils/channels";

const channelsStore = useChannelsStore();
const categoriesStore = useCategoriesStore();
categoriesStore.load();

const designPackagesStore = useDesignPackagesStore();

const props = defineProps<{
    record: Models.Record,
    channelId?: number,
    autoUpdateTitle?: boolean,
}>();

const channelId = props.channelId ?? props.record?.channel_id;

const model = defineModel<Models.Record>();
const data = ref<Models.Record>(props.record ?? model.value);
watch(() => data, () => {
    model.value = data.value;
}, {deep: true})

watch(() => model, () => {
    data.value = model.value;
}, {deep: true})

const emit = defineEmits<{
    (e: 'change'): void
}>();

const updateTitle = () => {
    const date = {
        year: data.value.year,
        year_start: data.value.year_start,
        year_end: data.value.year_end,
    }

    let channel = channelId ? channelsStore.findById(parseInt(channelId)) : null;
    if (channel) {
        channel = {
            ...channel,
            name: getNameByDate(channel, date)
        }
    }

    data.value.title = generateInterprogramTitle({
        type: 'interprogram',
        channel,
        short_description: data.value.short_description,
        date
    }, data.value.interprogram_type ? categoriesStore.findById(parseInt(data.value.interprogram_type)) : null);
}
const designPackages = computed(() => {
    return [
        {
            id: -1,
            text: 'Не выбрано',
        },
        ...(designPackagesStore.designPackages[channelId] ?? []).map(designPackage => {
            return {
                id: designPackage.id,
                text: designPackage.name !== '' ? designPackage.name : designPackage.years_range,
            }
        }),
    ]
});

watch(() => [
    data.value.interprogram_type,
    data.value.short_description,
    data.value.year,
    data.value.year_start,
    data.value.year_end,
], () => {
    emit('change');
    if (props.autoUpdateTitle) {
        updateTitle();
    }
});

if (props.autoUpdateTitle) {
    updateTitle();
}
</script>
