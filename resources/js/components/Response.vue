<template>
    <div class="response" v-if="data" v-html="data.text" :class="{'response--light': light, 'response--success': data.status === 1, 'response--error': data.status === 0}"></div>
</template>
<script lang="ts" setup>
defineProps< {
    data?: Forms.Response,
    light?: boolean
}>();
</script>
