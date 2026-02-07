<template>
    <input class="input" :type="type" v-model="model" />
</template>
<script lang="ts" setup>
const props = withDefaults(defineProps<{
    type?: string,
    value?: string
}>(), {
    type: 'date'
});


const model = defineModel({
    get(value: string) {
        if (!value) {
            return '';
        }
        const date = new Date(value);
        if (props.type === 'datetime-local') {
            return date.toISOString().slice(0, 16);
        }
        return date.toISOString().split('T')[0];
    },
    set(value) {
        return value;
    },
});

if (props.value) {
    model.value = props.value;
}
</script>
