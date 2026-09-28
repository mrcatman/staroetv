<template>
    <div class="response" v-if="data" :class="{'response--light': light, 'response--success': data.status === 1, 'response--error': data.status === 0}">
        {{data.text}}
        <a v-if="data.link" target="_blank" :href="data.link.url">{{data.link.text}}</a>
    </div>
</template>
<script lang="ts" setup>
defineProps< {
    data?: Forms.Response,
    light?: boolean
}>();
</script>
