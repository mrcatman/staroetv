<template>
    <div class="channels-order-manager">
        <div class="row">
            <div class="tabs">
                <a class="tab" :class="{'tab--active': type === 'tv'}" @click="type = 'tv'">ТВ</a>
                <a class="tab" :class="{'tab--active': type === 'radio'}"
                   @click="type = 'radio'">Радио</a>
            </div>
            <div class="tabs">
                <a class="tab" v-for="(categoryName, categoryId) in channelCategories" :class="{'tab--active': category === categoryId}" @click="category = categoryId">
                    {{categoryName}}
                </a>
            </div>
        </div>

        <div class="form">
            <Preloader v-if="loading" />
            <draggable v-model="channelsList" class="channels-order-manager__items">
                <div class="channels-order-manager__item" v-show="showChannel(channel)" v-for="channel in channelsList" :key="channel.id">
                    <a target="_blank" :href="getUrl(channel)">
                        {{ channel.name }}
                    </a>
                </div>
            </draggable>
            <br>
            <div class="form__bottom">
                <a @click="saveOrder()" class="button button--light">Сохранить</a>
                <response :light="true" :data="response"/>
            </div>
        </div>
    </div>
</template>
<style lang="scss">
.channels-order-manager {
    &__item {
        background: var(--bg-darker);
        border: 1px solid var(--border-color);
        padding: .75em;
        margin: 0 0 .5em;
        font-size: 1.125em;
    }
}
</style>
<script setup lang="ts">
import { ref } from 'vue';
import draggable from 'vuedraggable';
import Response from '../Response.vue';
import Preloader from "../Preloader.vue";
import { filterChannel, channelCategories } from "@/utils/channels.js";

const props = defineProps<{
    channels: Models.Channel[];
}>();

const channelsList = ref<Models.Channel[]>([...props.channels]);
const type = ref<string>('tv');
const category = ref<string>('federal');
const response = ref<Forms.Response | null>(null);
const loading = ref<boolean>(false);

const saveOrder = () => {
    loading.value = true;
    const order: { [key: number]: number } = {};
    channelsList.value.forEach((channel, index) => {
        order[channel.id] = index;
    });
    $.post(route('admin.channels.order.save'), {order}).done(res => {
        loading.value = false;
        response.value = res;
    }).fail((xhr) => {
        loading.value = false;
        const error = xhr.responseJSON;
        response.value = {status: 0, text: error.message === "" ? "Неизвестная ошибка" : error.message};
    })
};

const showChannel = (channel: Models.Channel) => {
    if (type.value === 'tv' && channel.is_radio) {
        return false;
    }
    if (type.value === 'radio' && !channel.is_radio) {
        return false;
    }
    return filterChannel(channel, category.value);
};

const getUrl = (channel: Models.Channel) => {
    return route(channel.is_radio ? 'radio-stations.show' : 'channels.show', channel.url ?? channel.id);
};
</script>
