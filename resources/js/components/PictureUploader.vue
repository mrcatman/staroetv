<template>
    <div class="picture-uploader" :class="{'picture-uploader--light': light, 'picture-uploader--small': small}" >
        <modal ref="pictureModalElement" :nopadding="true" title="Выбор картинки" :loading="loadingList" class="modal">
            <div class="picture-uploader__list">
                <div @click="setPicture(picture)" class="picture-uploader__item" v-for="(picture, $index) in picturesList" :key="$index">
                    <div class="picture-uploader__item__image" :style="{backgroundImage: `url(${picture.url})`}"></div>
                    <div class="picture-uploader__item__date">Дата загрузки: {{picture.created_at}}</div>
                </div>
            </div>
        </modal>
        <modal ref="URLModalElement" title="Загрузка по URL" :loading="URLModal.loading" class="modal">
            <div class="input-container input-container--vertical">
                <label class="input-container__label">Введите URL</label>
                <div class="input-container__inner">
                    <input class="input" v-model="URLModal.address"/>
                </div>
            </div>
            <div class="form__bottom">
                <a @click="loadPictureByURL()" class="button button--light">Загрузить</a>
                <Response :light="true" :data="URLModal.response"/>
            </div>
        </modal>
        <input type="hidden" :value="pictureData.id" :name="name"/>
        <div class="picture-uploader__inner">
            <a class="picture-uploader__reset"  v-if="pictureData && pictureData.url" @click="pictureData = {}">x</a>
            <div class="picture-uploader__image" v-if="pictureData && pictureData.url" :style="{backgroundImage: `url(${pictureData.url})`}"></div>
            <div class="picture-uploader__preloader" v-show="status === -1"></div>
        </div>
        <div class="picture-uploader__buttons">
            <label class="button" :class="{'button--light': light}">Загрузить <input style="display:none" @change="onFileInputChange" type="file" /></label>
            <a class="button" :class="{'button--light': light}" v-if="channelid" @click="getPictures()">Выбрать</a>
            <a class="button" :class="{'button--light': light}" @click="$refs.URLModalElement.show()">URL</a>

        </div>
    </div>
</template>
<style lang="scss">

    .picture-uploader {
        display: flex;
        align-items: center;
        background: #000;
        padding: .5em;
        &--light {
            padding: 0;
            background: none;
        }


        &__reset {
            background: linear-gradient(180deg, #513b27, #725336 49%, #291e13 50%, #2c1f14);
            width: 1em;
            height: 1em;
            position: absolute;
            top: .25em;
            right: .25em;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #c4bd97;
            cursor: pointer;
            text-decoration: none!important;
            line-height: 0;
        }
        &--light &__reset {
            background: linear-gradient(#fafafa, #fafafa 49%, #eee 50%, #eee);
        }

        &__image {
            width: 100%;
            height: 100%;
            background-position: center center;
            background-repeat: no-repeat;
            background-color: #555;
            background-size: 85%;
        }
        &__inner {
            width: 7.5em;
            height: 7.5em;
            background: rgba(255, 255, 255, 0.1);
            border: 1px dashed #c4bd97;
            margin: 0 1em 0 0;
            position: relative;
        }
        &__buttons {
            display: flex;
            flex-direction: column;
            text-align: center;
            .button {
                margin: 0 0 .5em;
            }
        }
        &__preloader {
            width: 3.5em;
            height: 3.5em;
            border: .5em solid #c4bd97;
            border-top-color: #0000;
            border-radius: 50%;
            position: absolute;
            top: 1.5em;
            left: 1.5em;
            animation: pictureUploaderPreloader 2s infinite;
        }
        &__list {
            overflow: auto;
            height: 100%;
        }

        &__item {
            color: #000;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #c1c1c1;
            padding: 1em;
            background: linear-gradient(#eee, #e1e1e1);
            cursor: pointer;
            &:hover {
                background: linear-gradient(#f9f9f9, #e3e3e3);
            }

            &__image {
                width: 7em;
                height: 4em;
                background-size: contain;
                background-position: center center;
                background-repeat: no-repeat;
            }
        }
        &--small &__inner {
            width: 3.5em;
            height: 3.5em;
        }
        &--small &__buttons {
            flex-direction: row;
        }
        &--small &__preloader {
            width: 2em;
            height: 2em;
            top: .25em;
            left: .25em;
        }
    }
    @keyframes pictureUploaderPreloader {
        0% {
            transform: rotate(0deg);
        }
        100% {
            transform: rotate(360deg);
        }
    }
</style>
<script>
    import Modal from './Modal';
    import Response from "./Response";
    let extensions = ['png', 'jpg', 'jpeg', 'gif', 'svg'];
    export default {
        components: {
            Response,
            Modal
        },
        watch: {
            pictureData(newData) {
                if (this.returnPath) {
                    this.$emit('input', newData.url);
                } else {
                    this.$emit('input', newData.id);
                }
            }
        },
        methods: {
            loadPictureByURL() {
                this.URLModal.loading = true;
                let data = {url: this.URLModal.address};
                if (this.channelid) {
                    data.channel_id = this.channelid;
                }
                if (this.tag) {
                    data.tag = this.tag;
                }
                $.post('/upload/pictures/by-url', data) .done((res) => {
                    this.URLModal.loading = false;
                    this.URLModal.response = res;
                    if (res.status) {
                        this.status = 1;
                        this.pictureData = res.data.picture;
                        this.$refs.URLModalElement.hide();
                    } else {
                        alert(res.text);
                    }
                }).fail((e) => {
                    this.URLModal.loading = false;
                    alert(e.responseJSON.message);
                })
            },
            setPicture(data) {
                this.pictureData = data;
                this.$refs.pictureModalElement.hide();
            },
            getPictures() {
                this.$refs.pictureModalElement.show();
                this.loadingList = true;
                $.get(`/upload/pictures/getbychannel/${this.channelid}`)
                    .done((res) => {
                        this.loadingList = false;
                        if (res.status) {
                            this.picturesList = res.data.pictures;
                        } else {
                            alert(data.text);
                        }
                    })
            },
            onFileInputChange(e) {
                let files = e.target.files;
                if (files && files[0]) {
                    let image = files[0];
                    let re = /(?:\.([^.]+))?$/;
                    let ext = re.exec(image.name)[1];
                    ext = ext.toLowerCase();
                    if (extensions.indexOf(ext) !== -1) {
                        let fd = new FormData();
                        if (this.channelid) {
                            fd.append('channel_id', this.channelid ? this.channelid : '');
                        }
                        if (this.tag) {
                            fd.append('tag', this.tag ? this.tag : '');
                        }
                        fd.append('picture', image);
                        this.status = -1;
                        $.ajax({
                            url: '/upload/pictures',
                            data: fd,
                            processData: false,
                            contentType: false,
                            type: 'POST',
                            success: (data) => {
                                if (data.status) {
                                    this.status = 1;
                                    this.pictureData = data.data.picture;
                                } else {
                                    alert(data.text);
                                }

                            },
                            error: (e) => {
                                this.status = -2;
                                alert(e.responseJSON.message);
                            }
                        });
                    } else {

                    }
                }
            }
        },
        props: {
            small: {

            },
            light: {

            },
            returnPath: {

            },
            value: {

            },
            tag: {

            },
            name: {

            },
            data: {

            },
            channelid: {

            },

        },
        data() {
            return {
                URLModal: {
                    address: '',
                    visible: false,
                    loading: false,
                    response: null
                },
                picturesList: [],
                showModal: false,
                loadingList: false,
                pictureData: this.data ? this.data : {},
                status: -2,
            }
        },
        mounted() {
            if (this.returnPath) {
                if (this.value) {
                    if (typeof this.value === "object") {
                        this.pictureData = this.value;
                    } else {
                        this.pictureData = {
                            url: this.value
                        }
                    }
                }
            }
        },
    }
</script>
