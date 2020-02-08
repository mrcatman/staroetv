<template>
    <div class="channel-names">
        <input type="hidden" name="channel_names" :value="namesJson" />
        <div class="channel-names__inner">
            <div  v-for="(name, $index) in this.names" :key="$index">
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
                    <div class="col">
                        <div class="input-container input-container--vertical">
                            <label class="input-container__label">Лого</label>
                            <div class="input-container__inner channel-names__picture-uploader-container">
                                <PictureUploader :key="name.id" :light="true" tag="logo" :data="name.logo" v-model="name.logo_id" :channelid="channelid"/>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row channel-names__row">
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
        background: linear-gradient(rgba(55, 52, 47, 0.67), rgba(0, 0, 0, 0.5));
        padding: .5em 1em 1em;
        flex: 1;
        background: #eee;
        color: #000;
        font-family: "Roboto", sans-serif;
        font-size: 1.25em;
        border: 1px dashed #555;
        &__bottom {
            font-size: .75em;
            background: #ddd;
            margin: 1em -1.25em -1.25em;
            padding: 1em;
            border-top: 1px dashed #555;
        }
        &__datepicker-container {
            margin: -2.25em .5em 0 .5em;
        }
        &__picture-uploader-container {
            font-size: .875em;
        }
        &__row {
            margin: -1em 0;
            border-bottom: 1px dashed #ccc;
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
