<template>
    <div class="channels-order-manager">
        <div class="admin-panel__heading-container">
            <div class="admin-panel__heading">Управление порядком каналов</div>
            <div class="tabs">
                <a class="tab" :class="{'tab--active': channelType === 'tv'}" @click="channelType = 'tv'">ТВ</a>
                <a class="tab" :class="{'tab--active': channelType === 'radio'}" @click="channelType = 'radio'">Радио</a>
            </div>
            <div class="tabs">
                <a class="tab" :class="{'tab--active': type === 'federal'}" @click="type = 'federal'">Федеральные</a>
                <a class="tab" :class="{'tab--active': type === 'regional'}" @click="type = 'regional'">Региональные</a>
                <a class="tab" :class="{'tab--active': type === 'abroad'}" @click="type = 'abroad'">Зарубежные</a>
                <a class="tab" :class="{'tab--active': type === 'other'}" @click="type = 'other'">Другие</a>
            </div>
        </div>
        <div class="admin-panel__main-content">
            <div class="form">
                <div class="form__preloader" v-if="loading"></div>
                <draggable v-model="channelsList" class="channels-order-manager__items">
                    <div class="channels-order-manager__item" v-show="showChannel(channel)" v-for="channel in channelsList" :key="channel.id">{{channel.name}}</div>
                </draggable>
                <br>
                <div class="form__bottom">
                    <a @click="saveOrder()" class="button button--light">Сохранить</a>
                    <response :light="true" :data="response"/>
                </div>
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
    import Response from '../Response'

    export default {
        computed: {

        },
        methods: {
            saveOrder() {
                this.loading = true;
                let order = {};
                this.channelsList.forEach((channel, index) => {
                    if (channel.is_federal && !channel.is_radio) {
                        console.log(channel.name, index);
                    }

                    order[channel.id] = index;
                });
                $.post('/admin/channels/order', {order}).done(res => {
                    this.loading = false;
                    this.response = res;
                }).fail((xhr) => {
                    this.loading = false;
                    let error = xhr.responseJSON;
                    this.response = {status: 0, text: error.message === "" ? "Неизвестная ошибка" : error.message};
                })
            },
            showChannel(channel) {
                if (this.channelType === 'tv' && channel.is_radio) {
                    return;
                }
                if (this.channelType === 'radio' && !channel.is_radio) {
                    return;
                }
                if (this.type === 'federal') {
                    return channel.is_federal;
                }
                if (this.type === 'regional') {
                    return channel.is_regional;
                }
                if (this.type === 'abroad') {
                    return channel.is_abroad;
                }
                if (this.type === 'other') {
                    return !channel.is_federal && !channel.is_regional && !channel.is_abroad;
                }
                return false;
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
                channelsList: this.channels,
                channelType: 'tv',
                type: 'federal',
                response: null,
                loading: false
            }
        },
        mounted() {

        },
        components: {
            Response,
            draggable
        }
    }
</script>
