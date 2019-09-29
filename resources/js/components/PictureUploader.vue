<template>
    <div class="picture-uploader" :class="{'picture-uploader--light': light}" >
        <div class="modal" v-show="showModal">
            <div class="modal__inner">
                <div class="modal__header">
                    <div class="modal__header__title">Выбор картинки</div>
                    <div class="modal__header__close" @click="showModal = false">(x)</div>
                </div>
                <div class="modal__content">
                    <div class="picture-uploader__loading-list" v-if="loadingList">Загрузка...</div>
                    <div class="picture-uploader__list" v-else>
                        <a @click="setPicture(picture)" class="picture-uploader__item" v-for="(picture, $index) in picturesList" :key="$index">
                            <div class="picture-uploader__item__image" :style="{backgroundImage: `url(${picture.url})`}"></div>
                            <div class="picture-uploader__item__date">Дата загрузки: {{picture.created_at}}</div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <input type="hidden" :value="pictureData.id" :name="name"/>
        <div class="picture-uploader__inner">
            <a class="picture-uploader__reset"  v-if="pictureData && pictureData.url" @click="pictureData = {}">x</a>
            <div class="picture-uploader__image" v-if="pictureData && pictureData.url" :style="{backgroundImage: `url(${pictureData.url})`}"></div>
            <div class="picture-uploader__preloader" v-show="status === -1"></div>
        </div>
        <div class="picture-uploader__buttons">
            <label class="button" :class="{'button--light': light}">Загрузить <input style="display:none" @change="onFileInputChange" type="file" /></label>
            <a class="button" :class="{'button--light': light}" v-if="channelid" @click="getPictures()">Выбрать</a>
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
            background-size: contain;
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
            align-items: flex-start;
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
    let extensions = ['png', 'jpg', 'jpeg', 'gif', 'svg'];
    export default {
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
            setPicture(data) {
                this.pictureData = data;
                this.showModal = false;
            },
            getPictures() {
                this.showModal = true;
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
                        if (this.type) {
                            fd.append('type', this.type ? this.type : '');
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
            light: {

            },
            returnPath: {

            },
            value: {

            },
            type: {

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
                    this.pictureData = {
                        url: this.value
                    }
                }
            }
        },
        components: {

        }
    }
</script>
