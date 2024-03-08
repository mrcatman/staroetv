<template>
    <div class="device-uploader">
        <div class="form__preloader" v-if="loading">
            <img src="/pictures/ajax.gif">
        </div>
        <snackbar ref="snackbar"></snackbar>
        <div class="device-uploader__top">
            <div class="inputs-line">
                <div class="inputs-line__item">
                    <div class="inputs-line__item__title">Канал</div>
                    <select2 theme="default" :options="channelOptions" @input ="e => loadInterprogramPackages()" v-model="channelId"></select2>
                    <a class="input-container__toggle-button" @click="loadChannels()">Перезагрузить</a>
                </div>
            </div>
        </div>
        <div class="device-uploader__sections">
            <div class="device-uploader__section" v-for="(section, $index) in sections" :key="$index">
                <div class="inputs-line">
                    <div class="inputs-line__item">
                        <div class="inputs-line__item__title">Выбор файлов</div>
                        <input type="file" multiple @change="(e) => addFiles(e, section)" />
                    </div>
                    <div class="inputs-line__item">
                        <div class="inputs-line__item__title">Пакет оформления</div>
                        <select2 theme="default" :options="interprogramOptions" v-model="interprogramPackageId"></select2>
                        <a  class="input-container__toggle-button"@click="loadInterprogramPackages(section, true)">Перезагрузить</a>
                    </div>
                </div>
                <div class="inputs-line" v-for="(file, $index2) in section.files" :key="$index2">
                    <div class="inputs-line__item">
                        <div class="input-container" >
                           <div class="input-container__inner">
                                <input class="input" v-model="file.name" value=""/>
                                <span class="input-container__message"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col col--button">
                        <a class="button" @click="section.files.splice($index2, 1)">Удалить</a>
                    </div>
                </div>
            </div>
        </div>
        <a class="button" @click="addSection()">Добавить раздел</a>
    </div>
</template>
<style lang="scss">
    .device-uploader {
        &__section {
            margin: 2em 0;
            border-top: 1px solid var(--border-color);
            padding: 1em 0 0;
        }
    }
</style>
<script>
    import Snackbar from "./Snackbar";
    export default {
        components: {Snackbar},
        data() {
            return {
                loading: false,
                channels: [],
                interprogramPackages: [],
                categories: [],
                sections: [],
                channelId: null,
                interprogramPackageId: null,
            }
        },
        mounted() {
            this.loadCategories();
            this.loadChannels();
        },
        computed: {
            channel() {
                return this.channels.filter(channel => channel.id == this.channelId)[0];
            },
            interprogramOptions() {
                let options = [];
                this.interprogramPackages.forEach(packageItem => {
                    options.push({id: packageItem.id, text: packageItem.name || packageItem.years_range})
                });
                return options;
            },
            interprogramTypes() {
                let categories = this.categories;
                if (categories.length === 0) {
                    return [];
                }
                categories = categories.filter(category => category.type === 'interprogram').map(category => {
                    return {id: category.id, text: category.name}
                });
                categories.unshift({
                    id: -1,
                    text: '-'
                });
                return categories;
            },
            channelOptions() {
                let data = [];
                this.channels.forEach(channel => {
                    data.push({id: channel.id, text: channel.name});
                });
                return data;
            },
            allChannelNames() {
                let names = {};
                this.channels.forEach(channel => {
                    names[channel.name] = channel.id;
                    if (channel.names) {
                        channel.names.forEach(channelName => {
                            names[channelName.name] = name.channel_id;
                        })
                    }
                });
                return names;
            },
        },
        methods: {
            uploadFile(record) {
                return new Promise((resolve, reject) => {
                    if (!record || !record.file) {
                        resolve();
                    }
                    let fd = new FormData();
                    fd.append('record', record.file);
                    this.$set(record.upload, 'status', -1);
                    $.ajax({
                        xhr: () => {
                            let xhr = new window.XMLHttpRequest();
                            xhr.upload.addEventListener("progress",  (evt) => {
                                if (evt.lengthComputable) {
                                    let percentComplete = evt.loaded / evt.total;
                                    percentComplete = parseInt(percentComplete * 100);
                                    this.$set(record.upload, 'percent', percentComplete);
                                }
                            }, false);
                            return xhr;
                        },
                        url: '/records/upload',
                        type: "POST",
                        processData: false,
                        data: fd,
                        contentType: false,
                    }).done(res => {
                        this.$set(record.upload, 'status', 1);
                        console.log('done', res);
                        if (res.status) {
                            this.$set(record.upload, 'url',  res.data.url);

                            this.$set(record.upload, 'screenshot', res.data.screenshot);
                        } else {
                            this.$set(record.upload, 'status', 0);
                            reject();
                            this.$refs.snackbar.show(res);
                        }
                    }).fail(e => {
                        this.$set(record.upload, 'status', 0);
                        console.log('fail', e);
                        this.$refs.snackbar.show({
                            status: 0,
                            text: e.responseJSON ? (e.responseJSON.message && e.responseJSON.message !== "" ? e.responseJSON.message : e.responseJSON.exception) : "Ошибка загрузки"
                        });
                        reject();
                    });
                })
            },
            addFiles(e, section) {
                if (e.target.files) {
                    Array.from(e.target.files).forEach(file => {
                        let name = file.name.replace(/\.[^/.]+$/, "");
                        let splitted = name.split('_');
                        if (splitted.length > 1) {
                            let type = splitted[0];
                            if (type === "РЗ") {
                                type = "Рекламная заставка";
                            }
                            if (type === "З") {
                                type = "Заставка";
                            }
                            let date = splitted[1];
                            if (this.channel) {
                                name = `${type} (${this.channel.name}, ${date})`;
                            } else {
                                name = `${type} (${date})`;
                            }

                            if (splitted.length > 2) {
                                name = `${name} ${splitted[2]}`;
                            }
                        }
                        section.files.push({
                            file,
                            upload: {
                                status: -2,
                            },
                            data: {
                                name,
                            }
                        });
                    })
                }
            },
            addSection() {
                this.sections.push({
                    channel_id: -1,
                    interprogram_package_id: -1,
                    files: []
                })
            },
            save() {

            },
            loadInterprogramPackages( forceReload = false) {
                $.get('/channels/'+this.channelId+'/graphics/ajax').done(res => {
                    this.interprogramPackages = res.data.graphics;
                })
            },
            loadCategories() {
                $.get('/records/categories').done(res => {
                    this.categories = res.data.categories;
                })
            },

            loadChannels() {
                $.get('/channels/ajax').then(res => {
                    this.channels = res.data.channels;
                })
            },
        }
    }
</script>
