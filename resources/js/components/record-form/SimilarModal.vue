<template>
    <modal ref="similar" title="Похожие записи" v-slot="{hide}">
        <div class="form__content">
            <div class="modal-window__text">
                Проверьте, возможно эта запись уже есть на сайте. Если ваша запись более полная или качественная -
                нажмите "Продолжить"
            </div>
            <div class="records-list similar-modal__list">
                <records-item v-for="record in similar" :key="record.id" :record="record"/>
            </div>
            <div class="similar-modal__buttons">
                <button class="button" @click="hide(); markSimilarAsChecked()">Продолжить</button>
                <button class="button button--light" @click="hide();">Отмена</button>
            </div>
        </div>
    </modal>
</template>
<style lang="scss" scoped>

.similar-modal {
    &__list {
        width: 100%;
        font-size: 1.25em;
    }

    &__buttons {
        margin-top: 1em;
        display: flex;
        gap: var(--col-margin);
    }
}
</style>
<script setup lang="ts">
import Modal from "@/components/Modal.vue";
import RecordsItem from "@/components/records/RecordsItem.vue";
import { useTemplateRef } from "vue";

defineProps<{
    similar: Models.Record[],
}>();

const emit = defineEmits<{ (e: 'mark'): void }>();

const markSimilarAsChecked = () => {
    emit('mark');
}

const similarRef = useTemplateRef<typeof Modal>('similar');
const show = () => {
    similarRef.value?.show({backdrop: true});
}

defineExpose({show});
</script>
