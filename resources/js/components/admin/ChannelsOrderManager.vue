<template>
    <div class="channels-order-manager">
        <div class="row">
            <div class="tabs">
                <a class="tab" :class="{'tab--active': type === 'tv'}" @click="channelType = 'tv'">ТВ</a>
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
<script>
import draggable from 'vuedraggable'
import Response from '../Response.vue'
import Preloader from "../Preloader.vue";

import { filterChannel, channelCategories } from "@/utils/channels.js";

export default {
    methods: {
        saveOrder() {
            this.loading = true;
            const order = {};
            this.channelsList.forEach((channel, index) => {
                order[channel.id] = index;
            });
            $.post(route('admin.channels.order.save'), {order}).done(res => {
                this.loading = false;
                this.response = res;
            }).fail((xhr) => {
                this.loading = false;
                let error = xhr.responseJSON;
                this.response = {status: 0, text: error.message === "" ? "Неизвестная ошибка" : error.message};
            })
        },
        showChannel(channel) {
            if (this.type === 'tv' && channel.is_radio) {
                return;
            }
            if (this.type === 'radio' && !channel.is_radio) {
                return;
            }
            return filterChannel(this.category);
        },
        getUrl(channel) {
            return route(channel.is_radio ? 'radio-stations.show' : 'channels.show', channel.url ?? channel.id);
        }
    },
    props: {
        channels: {
            type: Array,
            required: true,
        },
    },
    data() {
        return {
            channelCategories,

            channelsList: this.channels,
            type: 'tv',
            category: 'federal',
            response: null,
            loading: false
        }
    },
    mounted() {

    },
    components: {
        Preloader,
        Response,
        draggable
    }
}
</script>
