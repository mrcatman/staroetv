<template>
    <div class="channel-names">
        <input type="hidden" name="channel_names" :value="namesJson" />
        <div class="channel-names__inner">
            <div class="channel-names__item" v-for="(name, $index) in this.names" :key="$index">
                <div class="row channel-names__row">
                    <div class="col">
                        <div class="input-container input-container--vertical">
                            <label class="input-container__label">Название</label>
                            <div class="input-container__inner">
                                <input v-model="name.name" class="input"/>
                            </div>
                        </div>
                        <a class="button button--light" @click="deleteItem($index)">Удалить</a>
                    </div>
                    <div class="col channel-names__datepicker-container">
                        <div class="input-container input-container--vertical">
                            <label class="input-container__label">Начальная дата</label>
                            <div class="input-container__inner">
                                <Datepicker v-model="name.date_start"/>
                            </div>
                        </div>
                    </div>
                    <div class="col channel-names__datepicker-container">
                        <div class="input-container input-container--vertical">
                            <label class="input-container__label">Конечная дата</label>
                            <div class="input-container__inner">
                                <Datepicker v-model="name.date_end"/>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row channel-names__row">
                    <div class="col channel-names__picture-uploader-col">
                        <div class="input-container input-container--vertical">
                            <label class="input-container__label">Лого</label>
                            <div class="input-container__inner channel-names__picture-uploader-container">
                                <PictureUploader :key="name.id" :light="true" tag="logo" :data="name.logo" v-model="name.logo_id" :channelid="channelid"/>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="input-container input-container--vertical">
                            <label class="input-container__label">Описание</label>
                            <div class="input-container__inner">
                                <textarea v-model="name.comment" class="input"></textarea>
                            </div>
                        </div>
                    </div>
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
        padding: 0;
        background: var(--bg-darker);
        border: 1px solid var(--border-color);
        font-family: unset;
        &__bottom {
            font-size: .75em;
            padding: 1em;
            margin: 0;
            border: none;
            background: var(--bg-darker-2);
        }
        &__datepicker-container {
            margin: -2.25em .5em 0 .5em!important;
        }
        &__picture-uploader-col {
            flex: .325;
            margin: 0 1.5em 0 0!important;
        }
        &__picture-uploader-container {

        }
        &__item {
            border-bottom: 1px solid var(--border-color);
        }
        &__row {
            box-sizing: border-box;
            margin: 0;
            padding: 1em 1.5em;
            border: none;
        }

        &__inner {
            font-size: .75em;
            margin: -1em 0 0;
        }
    }
</style>
<script>
    import Datepicker from 'vuejs-datepicker';
    import PictureUploader from './PictureUploader';

    export default {
        computed: {
            namesJson() {
                return JSON.stringify(this.names)
            }
        },
        methods: {
            deleteItem(index) {
                this.names.splice(index, 1);
                //this.$forceUpdate();
            },
            addItem() {
                let data = JSON.parse(JSON.stringify({
                    name: "",
                    date_start: new Date(),
                    date_end: new Date(),
                    logo_id: null
                }));
                this.names.push(data);
            }
        },
        props: {
            channelid: {

            },
            data: {
                type: Array,
                required: true
            }
        },
        data() {
            return {
                names: this.data || []
            }
        },
        mounted() {

        },
        components: {
            PictureUploader,
            Datepicker
        }
    }
</script>
