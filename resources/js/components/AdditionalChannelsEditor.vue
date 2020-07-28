<template>
    <div class="additional-channels">
        <input type="hidden" name="additional_channels" :value="channelsJson" />
        <div class="additional-channels__inner">
            <div  v-for="(channelItem, $index) in this.additionalChannels" :key="$index">
                <div class="row additional-channels__row">
                    <div class="col">
                        <div class="input-container input-container--vertical">
                            <label class="input-container__label">Канал</label>
                            <div class="input-container__inner">
                                <select class="select-classic" v-model="channelItem.channel_id">
                                    <option :value="channel.id" v-for="(channel, $index) in channelsList" :key="channel.id">{{channel.name}}</option>
                                </select>
                            </div>
                        </div>
                        <a class="button button--light" @click="deleteItem($index)">Удалить</a>
                    </div>
                    <div class="col additional-channels__datepicker-container">
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
                </div>
            </div>
        </div>
        <div class="additional-channels__bottom">
            <a class="button button--light" @click="addItem()">Добавить доп.канал</a>
        </div>

    </div>
</template>
<style lang="scss">
    .additional-channels {
        flex: 1;
        margin: 1em 0;
        background: var(--bg-darker);
        font-size: 1.25em;
        border: 1px solid var(--border-color);
        &__bottom {
            font-size: .75em;
            background: var(--bg-darker-2);
            padding: 1em;
        }
        &__datepicker-container {
            margin: -2.25em .5em 0 .5em!important;
        }
        &__picture-uploader-container {
            font-size: .875em;
        }
        &__row {
            width: calc(100% - 2em);
            margin: 0;
            border-bottom: 1px solid var(--border-color);
            padding: 1em;
         }
        &__inner {
            font-size: .75em;
        }

    }
</style>
<script>
    import Datepicker from 'vuejs-datepicker';
    import PictureUploader from './PictureUploader';

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
                    date_start: new Date(),
                    date_end: new Date(),
                }));
                this.additionalChannels.push(data);
            }
        },
        props: {
            program_id: {},
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
            $.get('/channels/ajax').then(res => {
                this.channelsList = res.data.channels;
            })
        },
        components: {
            Datepicker
        }
    }
</script>
