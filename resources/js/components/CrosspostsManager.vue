<template>
    <div class="crossposts-manager">
        <Response :data="response"></Response>
        <div class="input-container">
            <label class="input-container__label">Время публикации</label>
            <div class="input-container__inner">
                <div class="input-container__element-outer">
                    <label class="input-container input-container--radio">
                        <input type="radio" v-model="publishNow" :value="true">
                        <div class="input-container--radio__element"></div>
                        <div class="input-container__label">Вручную</div>
                    </label>
                    <label class="input-container input-container--radio">
                        <input type="radio" v-model="publishNow" :value="false">
                        <div class="input-container--radio__element"></div>
                        <div class="input-container__label">Выбрать время</div>
                        <Datetimepicker v-if="!publishNow" v-model="post_time" firstDayOfWeek="1" format="DD/MM/YYYY H:i" />
                    </label>

                </div>

            </div>
        </div>
         <div class="input-container">
             <label class="input-container__label">Текст</label>
             <div class="input-container__inner">
                 <textarea class="input input--textarea" v-model="data.text"></textarea>
             </div>
         </div>
        <div class="input-container" v-show="servicesData.twitter && servicesData.twitter.active">
            <label class="input-container__label">Короткий текст (для твиттера)</label>
            <div class="input-container__element-outer">
                <div v-if="data.short_texts.length > 0" class="input-container__inner" v-for="(text, $index) in data.short_texts" :key="$index">
                    <div class="row">
                        <div class="col">
                            <textarea maxlength="280" class="input" v-model="data.short_texts[$index]"></textarea>
                        </div>
                        <div class="col--button">
                            <a @click="data.short_texts.splice($index)" class="button button--light">X</a>
                        </div>
                    </div>
                </div>
                <div class="crossposts-manager__add-text">
                     <a class="button" @click="addNewShortText()">Добавить</a>
                </div>
            </div>

        </div>
        <div class="input-container">
            <label class="input-container__label">Ссылка</label>
            <div class="input-container__inner">
                <input class="input" v-model="data.link"/>
            </div>
        </div>
        <div class="input-container">
            <label class="input-container__label">Описание ссылки</label>
            <div class="input-container__inner">
                <input class="input" v-model="data.link_text"/>
            </div>
        </div>


        <div class="crossposts-manager__media">
            <div class="crossposts-manager__media__title">Медиа</div>
            <div class="crossposts-manager__media__item" :key="$index" v-for="(item, $index) in data.media">
                <div class="inputs-line">
                    <div class="input-container crossposts-manager__media__left" v-if="item.type === 'video'">
                        <label class="input-container__label">Ссылка на видео</label>
                        <div class="input-container__inner">
                            <input class="input" v-model="item.value"/>
                        </div>
                    </div>
                    <div class="input-container crossposts-manager__media__left" style="margin-left:1em;" v-if="item.type === 'video' && item.value && item.value.length > 0 && item.value.indexOf('youtu') === -1">
                        <label class="input-container__label">Альтернативная ссылка (youtube)</label>
                        <div class="input-container__inner">
                            <input class="input" v-model="item.value_alt"/>
                        </div>
                    </div>
                    <div class="input-container crossposts-manager__media__left" v-if="item.type === 'picture'">
                        <label class="input-container__label">Картинка</label>
                        <div class="input-container__inner">
                            <PictureUploader tag="crosspost" :data="item.value" v-model="item.value" :returnPath="true" />
                        </div>
                    </div>
                    <div v-show="$index > 0" class="input-container crossposts-manager__media__right">
                        <label class="input-container__label">Описание</label>
                        <div class="input-container__inner">
                            <input class="input" v-model="item.text"/>
                        </div>
                    </div>
                    <div class="crossposts-manager__media__delete">
                        <a class="button" @click="data.media.splice($index, 1)">Удалить</a>
                    </div>
                </div>
            </div>

            <div class="crossposts-manager__media__add">
                <div class="buttons-row">
                    <a @click="data.media.push({type: 'video', value: null, text: ''})" class="button button--light">Добавить видео</a>
                    <a @click="data.media.push({type: 'picture', value: null, text: ''})" class="button button--light">Добавить картинку</a>
                </div>
            </div>
        </div>
        <button class="button" @click="save()">Сохранить</button>
        <div class="crossposts-manager__services">
            <div class="crossposts-manager__service" v-if="service.is_active && servicesData[service.id]" v-for="service in services" :key="service.id">
                <div class="crossposts-manager__service__texts">
                    <label class="input-container input-container--checkbox" >

                        <input type="checkbox" v-model="servicesData[service.id].active">
                        <div class="input-container--checkbox__element"></div>
                        <div class="input-container__label">{{service.name}}</div>
                    </label>
                    <div class="crossposts-manager__service__status">
                        Статус:
                        <span v-if="!servicesData[service.id].data || servicesData[service.id].data.status === -1">Не готово</span>
                        <span v-else-if="servicesData[service.id].data && servicesData[service.id].data.status === 0">Ошибка: <strong class="crossposts-manager__service__status__error">{{servicesData[service.id].data.error_log}}</strong></span>
                        <span v-else-if="servicesData[service.id].data && servicesData[service.id].data.status === 1">Готово</span>
                    </div>
                    <div v-if="servicesData[service.id].data && servicesData[service.id].data.links && servicesData[service.id].data.links.length > 0" class="crossposts-manager__links">
                        Ссылки: <a class="crossposts-manager__link" :href="link" target="_blank" v-for="(link, $index) in servicesData[service.id].data.links">[{{$index}}]</a>
                    </div>
                </div>
                <div class="crossposts-manager__service__buttons">
                    <div class="buttons-row">
                        <a class="button button--light" v-if="!servicesData[service.id].data || servicesData[service.id].data.status === -1"  @click="makePost(service.id)">Сделать пост</a>
                        <a class="button button--light" v-if="servicesData[service.id].data && service.can_edit_posts" @click="makePost(service.id)">Обновить пост</a>
                        <a class="button button--light" v-if="servicesData[service.id].data && !service.can_edit_posts" @click="makePost(service.id, true)">Сделать пост заново</a>
                        <a class="button button--light" v-if="servicesData[service.id].data && service.can_delete_posts && servicesData[service.id].data.post_ids &&  servicesData[service.id].data.post_ids !== ''" @click="deletePost(service.id)">Удалить пост</a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>
<style lang="scss">
    .crossposts-manager {
        &__service {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid var(--border-color);
            margin: .5em 0  0;
            padding: 0 0 1em;
        }
        &__services {
            border-top: 1px solid var(--border-color);
            margin-top: 1em;
        }
        &__add-text {
            margin: .5em 0 1em;
        }
        &__media {
            margin: 2em 0;

            &__title {
                font-size: 1.25em;
                font-weight: bold;
                margin: 0 0 .5em;
            }
            &__delete {
                margin: 1em 0 1em 1em;
            }
            &__left {
                flex: .5;
                .input-container__label {
                    min-width: 8.75em;
                }
            }
            &__right {
                flex: 1;
                margin-left: 3em;
                .input-container__label {
                    min-width: unset;
                }
            }

        }
    }
</style>
<script>
    import PictureUploader from './PictureUploader';
    import Modal from './Modal';
    import Response from './Response';
    import Snackbar from './Snackbar';
    import Datetimepicker from './Datetimepicker';
    export default {
        watch: {
            publishNow(isNow) {
                if (isNow) {
                    this.post_time = null;
                }
            }
        },
        components: {
            PictureUploader, Modal, Response, Snackbar, Datetimepicker
        },
        methods: {
            addNewShortText() {
               this.$set(this.data, 'short_texts', [...this.data.short_texts, '']);
            },
            deletePost(service) {
                $.post(`/crossposts/${this.id}/delete-post/${service}`).done(res => {
                    this.response = res;
                    this.$delete(this.servicesData[service], 'data');
                })
            },
            makePost(service, force = false) {
                $.post(`/crossposts/${this.id}/make-post/${service}${(force ? '?force=1' : '')}`).done(res => {
                    this.response = res;
                    this.$set(this.servicesData[service], 'data', res.data.post_connection);
                })
            },
           save() {
               let servicesList = [];
               this.services.forEach(service => {
                   if (this.servicesData[service.id].active) {
                       servicesList.push(service.id);
                   }
               });
               const url = this.id ? `/crossposts/${this.id}/edit` : '/crossposts/add';
               $.post(url, {
                   post_time: this.post_time,
                   data: this.data,
                   services: servicesList
               }).done(res => {
                   window.scrollTo(0, 0);
                   this.response = res;
                   if (res.status) {
                       if (!this.id) {
                           window.location.href = res.redirect_to;
                       }
                    //   this.id = res.data.crosspost.id;
                   }
               }).fail((xhr) => {
                   window.scrollTo(0, 0);
                   let error = xhr.responseJSON;
                   this.response = {
                       status: 0,
                       text: error.message ? error.message : "Неизвестная ошибка"
                   }
               });
           }
        },
        data() {
            return {
                id: this.crosspost ? this.crosspost.id : null,
                post_time: this.crosspost ? new Date(this.crosspost.post_ts * 1000) : null,
                response: null,
                servicesData: {},
                data: this.crosspost ? this.crosspost.post_data : {
                    short_texts: [''],
                    media: []
                },
                publishNow: !this.crosspost || !this.crosspost.post_ts,
            }
        },
         mounted() {

            if (this.data && !this.data.short_texts) {
                this.$set(this.data, 'short_texts', ['']);
            }
            let hasConnections = this.crosspost && this.crosspost.post_connections;
            this.services.forEach(service => {
                this.$set(this.servicesData, service.id, {
                    active: hasConnections ? false : service.is_active
                });
            });
            if (hasConnections) {
                this.crosspost.post_connections.forEach(postConnection => {
                    this.$set(this.servicesData, postConnection.service, {
                        active: true,
                        data: postConnection
                    });
                })
            }
        },
        props: ['crosspost', 'services']
    }
</script>
