<template>
    <div class="user-groups-select">
        <input type="hidden" :name="name" v-model="value"/>
        <div class="user-groups-select__default-settings" v-show="showDefaultSettings">
            <input-container checkbox label="Настройки по умолчанию">
                <input type="checkbox" v-model="defaultSettings">
                <div class="input-container--checkbox__element"></div>
            </input-container>
        </div>
        <div class="user-groups-select__items" v-show="!defaultSettings || !showDefaultSettings">
            <div class="user-groups-select__item" v-for="(group, $index) in groups" :key="$index">
                <input-container checkbox :label="group.name">
                    <input type="checkbox" v-model="dataByGroup[group.id]">
                    <div class="input-container--checkbox__element"></div>
                </input-container>
            </div>
            <div class="user-groups-select__item user-groups-select__item--all-groups">
                <input-container checkbox label="Все группы">
                    <input type="checkbox" v-model="allGroups" @change="onAllGroupsChange">
                    <div class="input-container--checkbox__element"></div>
                </input-container>
            </div>
        </div>
    </div>
</template>
<style lang="scss">
.user-groups-select {
    flex: 1;

    &__items {
        padding: 1em;
        display: grid;
        gap: 1em;
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        font-size: 0.875em;
        background: var(--inputs-color);
        border: 1px solid var(--border-color);
    }

    &__item {
        &--all-groups {
            color: blue;
        }
    }

    &__default-settings {
        margin: -.35em -.75em .5em;
    }
}
</style>
<script lang="ts" setup>
import { computed, onMounted, ref, watch } from "vue";
import InputContainer from "@/components/InputContainer.vue";

const props = defineProps<{
    name: string,
    data: any,
    groups: any[],
    showDefaultSettings?: boolean
}>();

const defaultSettings = ref<boolean>(props.data === "0" || props.data === 0);
const allGroups = ref<boolean>(true);
const dataByGroup = ref<{
    [key: string]: boolean
}>({});

const value = computed(() => {
    if (defaultSettings.value && props.showDefaultSettings) {
        return "0";
    }
    let groups = [];
    props.groups.forEach(group => {
        if (dataByGroup.value[group.id]) {
            groups.push(group.id)
        }
    });
    return groups.join(",");
});

const onAllGroupsChange = () => {
    props.groups.forEach(group => {
        dataByGroup.value[group.id] = allGroups.value;
    });
};

onMounted(() => {
    const splitted = props.data.split(",").filter(val => val.length > 0).map(val => parseInt(val));
    props.groups.forEach(group => {
        const groupVal = !props.data || props.data === "0" || splitted.indexOf(group.id) !== -1;
        dataByGroup.value = {
            ...dataByGroup.value,
            [group.id]: groupVal
        }
        if (!groupVal) {
            allGroups.value = false;
        }
    });
})
</script>
