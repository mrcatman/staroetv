<template>
    <component :is="checkbox ? 'label' : 'div'" class="input-container"
         :class="{'input-container--vertical': vertical, 'input-container--with-errors': !!errors, 'input-container--checkbox': checkbox}">
        <slot v-if="checkbox" />
        <component :is="!checkbox ? 'label' : 'div'" class="input-container__label" :class="{'input-container__label--small': labelSmall}">{{ label }}</component>
        <div  v-if="!checkbox" class="input-container__inner" :class="{'input-container__inner--with-button': withButton}">
            <div class="input-container__element-outer">
                <slot />
                <div class="input-container__description" v-if="$slots.description">
                    <slot name="description" />
                </div>
                <div class="input-container__toggle-buttons" v-if="$slots.toggleButtons">
                    <slot name="toggleButtons" />
                </div>
            </div>
            <span class="input-container__message">{{ errors }}</span>
        </div>

    </component>
</template>
<script lang="ts" setup>
defineProps<{
    label?: string,
    labelSmall?: boolean,
    errors?: string | string[],
    checkbox?: boolean,
    withButton?: boolean,
    vertical?: boolean,
}>();
</script>
