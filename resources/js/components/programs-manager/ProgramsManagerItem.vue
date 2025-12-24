<template>
    <div class="programs-manager__item" >
        <span class="programs-manager__item__name">{{ program.name }}</span>
        <div class="programs-manager__item__actions">
            <a :href="_route('programs.show', program.url ?? program.id)"
               target="_blank"
               class="programs-manager__item__action">
                На страницу
            </a>
            <a @click="emit('merge')" class="programs-manager__item__action">Объединить...</a>
        </div>
    </div>
</template>
<script lang="ts" setup>
defineProps<{
    program: Forms.Program
}>();
const emit = defineEmits<{ (e: 'merge'): void }>();
const _route = route;
</script>
