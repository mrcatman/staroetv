<template>
    <preloader v-if="loading" />
    <div v-else class="commercials-panel">
        <div class="commercials-panel__inner">
            <player-embed :record="record" />
            <div class="commercials-panel__buttons">
                <a class="button" @click="getRecord()">Другой ролик</a>
                <a target="_blank" :href="record.url" class="button button--light">На страницу</a>
                <a target="_blank" :href="_route(`records.${record.is_radio ? 'radio' : 'video'}.edit`, record.id)" class="button button--light">В редактор</a>
            </div>
        </div>


        <div class="form__content">
            <commercials-info-editor v-model="record"/>
        </div>
        <div class="form__bottom">
            <button class="button" :disabled="saving" @click="save(true)">Сохранить и перейти к другому ролику</button>
            <button class="button button--light" :disabled="saving" @click="save()">Сохранить</button>

        </div>
    </div>
</template>
<style lang="scss" scoped>
.commercials-panel {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: var(--col-margin);
    &__inner {
        display: flex;
        align-items: center;
        gap: var(--col-margin);
    }
    &__buttons {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: .25em;
    }
    :global(.plyr) {
        min-width: 640px;
    }
    iframe {
        width: 640px;
        height: 480px;
        margin-bottom: 1em;
    }
}
</style>
<script setup lang="ts">
import { onMounted, ref, nextTick } from "vue";
import Preloader from "@/components/Preloader.vue";
import CommercialsInfoEditor from "@/components/records-manager/CommercialsInfoEditor.vue";
import PlayerEmbed from "@/components/PlayerEmbed.vue";

const record = ref<Models.Record>();
const loading = ref(true);
const saving = ref(false);

const getRecord = async () => {
    loading.value = true;
    record.value = (await $.get(route('redactor.commercials.get-random'))).data.record;
    loading.value = false;
}

const save = async (goToNewRecord = false) => {
    saving.value = true;
    if (record.value.advertising_type === -1) {
        record.value.advertising_type = null;
    }

    await $.post(route('records.edit.commercials-info.save'), record.value);
    saving.value = false;
    if (goToNewRecord) {
        getRecord();
    }
}

onMounted(getRecord);

const _route = route;
</script>
