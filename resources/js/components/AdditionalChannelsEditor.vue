<template>
    <div class="additional-channels">
        <input type="hidden" name="additional_channels" :value="channelsJson" />
        <div class="additional-channels__inner">
            <div  v-for="(channelItem, $index) in this.additionalChannels" :key="$index">
                <div class="row ">
                    <div class="col">
                        <div class="input-container input-container--vertical">
                            <label class="input-container__label">{{is_radio ? 'Радио' : 'Канал'}}</label>
                            <div class="input-container__inner">
                                <select class="select-classic" v-model="channelItem.channel_id">
                                    <option :value="channel.id" v-for="(channel, $index) in channelsList" :key="channel.id">{{channel.name}}</option>
                                </select>
                            </div>
                        </div>

                    </div>
                    <div class="col ">
                        <div class="input-container input-container--vertical">
                            <label class="input-container__label">Название (если оно отличалось)</label>
                            <div class="input-container__inner">
                                <input v-model="channelItem.title" class="input"/>
                            </div>
                        </div>
                    </div>
                    <div class="col additional-channels__datepicker-container">
                        <div class="input-container input-container--vertical">
                            <label class="input-container__label">Начальная дата показа</label>
                            <div class="input-container__inner">
                                <Datepicker v-model="channelItem.date_start"/>
                            </div>
                        </div>
                    </div>
                    <div class="col additional-channels__datepicker-container">
                        <div class="input-container input-container--vertical">
                            <label class="input-container__label">Конечная дата показа</label>
                            <div class="input-container__inner">
                                <Datepicker v-model="channelItem.date_end"/>
                            </div>
                        </div>
                    </div>
                    <a class="button button--light" @click="deleteItem($index)">Удалить</a>
                </div>
            </div>
        </div>
        <div class="additional-channels__bottom">
            <a class="button button--light" @click="addItem()">Добавить доп.{{ is_radio ? 'радио' : 'канал' }}</a>
        </div>

    </div>
</template>
<style lang="scss">
    .additional-channels {
        flex: 1;

        &__inner {
            display: flex;
            flex-direction: column;
            gap: 2em;
        }
        &__bottom {
            margin-top: 2em;
        }

        &__datepicker-container {
            flex: .5;
        }

        &__row {
            width: calc(100% - 2em);
            margin: 0;
            border-bottom: 1px solid var(--border-color);
            padding: 1em;
         }


    }
</style>
<script>
    import Datepicker from 'vuejs-datepicker';

    export default {
        computed: {
            channelsJson() {
                return JSON.stringify(this.additionalChannels)
            }
        },
        methods: {
            deleteItem(index) {
                this.additionalChannels.splice(index, 1);
                //this.$forceUpdate();
            },
            addItem() {
                let data = JSON.parse(JSON.stringify({
                    title: "",
                    channel_id: null,
                    date_start: null,
                    date_end: null,
                }));
                this.additionalChannels.push(data);
            }
        },
        props: {
            program_id: {},
            is_radio: {},
            data: {
                type: Array,
                required: true
            }
        },
        data() {
            return {
                channelsList: [],
                additionalChannels: this.data || []
            }
        },
        mounted() {
            $.get(route(this.is_radio ? 'radio-stations.ajax' : 'channels.ajax')).then(res => {
                this.channelsList = res.data.channels;
            })
        },
        components: {
            Datepicker
        }
    }
</script>
