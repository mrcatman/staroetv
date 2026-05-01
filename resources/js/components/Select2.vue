<template>
    <select :name="name" :multiple="multiple" ref="el">
        <slot></slot>
    </select>
</template>
<script lang="ts" setup>
import { nextTick, onBeforeUnmount, onMounted, useTemplateRef, ref, watch, computed } from "vue";
import { language } from '@/utils/select2-language';

const props = defineProps<{
    options?: any,
    name?: string,
    theme?: 'default',
    customOptions?: any,
    multiple?: boolean
}>();

const select2 = ref();
const model = defineModel<string | number | string[]>();
const name = defineModel<string | number | string[]>('name');

const el = useTemplateRef('el');
const emit = defineEmits<{ (e: 'change'): void }>();

const allOptions = computed(() => {
    return {
        data: props.options || [],
        theme: props.theme,
        language,
        ...(props.customOptions || {})
    }
});

onMounted(() => {
    select2.value = $(el.value).select2(allOptions.value).val(model.value).trigger('change').on('change', function (e, params) {
        model.value = $(this).val();
        name.value = $(this).find(':selected').text();

        if (!params?.manual) {
            emit('change');
        }
    })[0];
})

watch(() => model.value, () => {
    if (props.multiple) {
        return;
    }
    $(el.value).val(model.value).trigger('change', {manual: true})
})

watch(() => props.options, async () => {
    $(el.value).empty().select2(allOptions.value);
    await nextTick();
    $(el.value).val(model.value).trigger('change', {manual: true})
})

onBeforeUnmount(() => {
    $(el.value).off().select2('destroy')
})

defineExpose({select2});
</script>
