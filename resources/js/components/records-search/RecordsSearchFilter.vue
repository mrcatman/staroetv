<template>
    <div class="records-search__filter">
        <div class="input-container input-container--vertical">
            <label class="input-container__label records-search__filter__label" @click="opened = !opened">
                <span v-if="showReset" class="records-search__filter__reset" @click.prevent.stop="emit('reset')">
                      <i class="fa fa-times"></i>
                </span>

                <span class="records-search__filter__title">{{ title }}</span>
                <i class="fa records-search__filter__arrow" :class="opened ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
            </label>
            <div class="horisontal-delimiter records-search__delimiter"></div>
            <div class="input-container__inner" v-show="opened">
                <div class="input-container__element-outer">
                    <slot></slot>
                </div>
            </div>
        </div>
    </div>
</template>
<style lang="scss" scoped>
@use "../../../sass/mixins" as *;

.records-search {
    &__filter {
        width: 100%;

        &__label {
            width: 100%;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: .5em;
            justify-content: space-between;
        }

        &__title {
            flex: 1;
        }

        &__reset {
            color: var(--text-lightest);
            @include hover(true);
            width: 1em;
            text-align: center;
        }
    }

    &__delimiter {
        margin: .25em 0;
    }
}
</style>
<script setup lang="ts">
const opened = defineModel<boolean>('opened');
const emit = defineEmits<{ (e: 'reset'): void }>();

defineProps<{
    title: string,
    showReset?: boolean,
}>();
</script>
