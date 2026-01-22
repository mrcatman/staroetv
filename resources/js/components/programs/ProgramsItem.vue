<template>
    <div class="program">
        <a target="_blank" :href="program.full_url" class="program__cover">
            <div class="program__cover__foreground" :style="`background-image: url(${program.cover_url})`"></div>
            <div class="program__cover__background" :style="`background-image: url(${program.cover_url})`"></div>
        </a>
        <a :href="program.full_url" class="program__name">{{program.name}}</a>
        <div v-if="program.channels_history" class="program__channels">
            <a v-for="channel in program.channels_history" :href="channel.url" class="program__channel__name">
                <img v-if="channel.logo" class="program__channel__logo" :src="channel.logo"/>
                {{channel.name}}
            </a>
        </div>
    </div>
</template>
<script lang="ts" setup>
defineProps<{
    program: Models.Program
}>();
</script>
