<template>
    <div class="channel-names">
        <input type="hidden" name="channel_names" :value="namesJson" />
        <div class="channel-names__inner">
            <div class="row" v-for="(name, $index) in this.names" :key="$index">
                <div class="col">
                    <div class="input-container input-container--vertical">
                        <label class="input-container__label">Название</label>
                        <div class="input-container__inner">
                            <input v-model="name.name" class="input"/>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="input-container input-container--vertical">
                        <label class="input-container__label">Начальная дата</label>
                        <div class="input-container__inner">
                            <Datepicker v-model="name.date_start"/>
                        </div>
                    </div>
                </div>
                <div class="col">
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
                        <div class="input-container__inner">
                            <PictureUploader type="logo" :data="name.logo" v-model="name.logo_id" :channelid="channelid"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <a class="button" @click="addItem()">Добавить еще пункт</a>
    </div>
</template>
<style lang="scss">
    .channel-names {
        background: linear-gradient(rgba(55, 52, 47, 0.67), rgba(0, 0, 0, 0.5));
        padding: .5em 1em 1em;
        flex: 1;
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
