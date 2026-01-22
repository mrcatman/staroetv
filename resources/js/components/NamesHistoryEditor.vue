<template>
    <div class="channel-names">
        <input type="hidden" name="channel_names" :value="namesJson"/>
        <div class="channel-names__inner">
            <div class="channel-names__item" v-for="(name, $index) in names" :key="$index">
                <div class="row channel-names__row">
                    <div class="col col--auto">
                        <div class="form__content">
                            <div class="input-container input-container--vertical">
                                <label class="input-container__label">Название</label>
                                <div class="input-container__inner">
                                    <input v-model="name.name" class="input"/>
                                </div>
                            </div>

                            <div class="input-container input-container--vertical">
                                <label class="input-container__label">Логотип</label>
                                <div class="input-container__inner channel-names__picture-uploader-container">
                                    <picture-uploader light :key="name.id" tag="logo" v-model="name.logo" :channel-id="channelId"/>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form__content">
                            <div class="input-container input-container--vertical">
                                <label class="input-container__label">Альтернативные названия для поиска</label>
                                <div class="input-container__inner">
                                    <vue-tags-input placeholder="Добавить название" v-model="name._tag" :tags="name.alternatives" @tags-changed="newTags => name.alternatives = newTags"/>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="input-container input-container--vertical">
                                        <label class="input-container__label">Начальная дата</label>
                                        <div class="input-container__inner">
                                            <Datepicker v-model:value="name.date_start"/>
                                        </div>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="input-container input-container--vertical">
                                        <label class="input-container__label">Конечная дата</label>
                                        <div class="input-container__inner">
                                            <Datepicker v-model:value="name.date_end"/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <a class="button button--light" @click="deleteItem($index)">Удалить</a>

                        </div>

                    </div>
                    <!--
                    <div class="col">
                        <div class="input-container input-container--vertical">
                            <label class="input-container__label">Описание</label>
                            <div class="input-container__inner">
                                <textarea v-model="name.comment" class="input"></textarea>
                            </div>
                        </div>
                    </div>
                    -->
                </div>
            </div>
        </div>
        <div class="channel-names__bottom">
            <a class="button button--light" @click="addItem()">Добавить еще пункт</a>
        </div>

    </div>
</template>
<style lang="scss">

.channel-names {
    flex: 1;
    font-size: 1.25em;
    font-family: unset;

    &__bottom {
        font-size: .75em;
        margin-top: 1em;
        border: none;
    }

    &__inner {
        display: flex;
        flex-direction: column;
        gap: 2em;
    }

    &__item {
        border-bottom: 1px solid var(--border-color);
        display: flex;
        flex-direction: column;
        gap: var(--col-margin);
        padding-bottom: 2em;
    }

    &__row {
        box-sizing: border-box;
        border: none;
    }

    &__inner {
        font-size: .75em;
    }
}
</style>
<script lang="ts" setup>
import {computed, ref} from "vue";

import Datepicker from './datepicker/components/Datepicker.vue';
import PictureUploader from './PictureUploader.vue';
import vueTagsInput from "@wslyhbb/vue3-tags-input";

const props = defineProps<{
    channelId?: number,
    data: Models.ChannelName[]
}>();

const names = ref<Partial<Models.ChannelName>[]>(props.data.map(name => {
    if (!name.alternatives || !Array.isArray(name.alternatives)) {
        name.alternatives = [];
    }
    return name;
}));
const namesJson = computed(() => {
    return JSON.stringify(names.value);
})

const addItem = () => {
    const name: Partial<Models.ChannelName> = {
        name: '',
        alternatives: [],
        date_start: new Date(),
        date_end: new Date(),
        logo_id: null
    };
    names.value.push({
        ...name
    });
}

const deleteItem = (index: number) => {
    names.value.splice(index, 1);
}
</script>
