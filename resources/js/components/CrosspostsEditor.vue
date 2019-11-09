<template>
    <div class="crossposts-editor">
        <div class="crossposts-editor__title">Постинг в соцсети</div>
        <div class="crossposts-editor__networks">
            <div class="crossposts-editor__network" v-for="(network, $index) in networks" :key="$index">
                <div class="form__preloader" v-if="statusesByNetwork[network.id] === -2"><img src="/pictures/ajax.gif"></div>
                <span class="crossposts-editor__network__name">{{network.name}}</span>
                <span class="crossposts-editor__network__status" :class="'crossposts-editor__network__status--'+statusClasses[statusesByNetwork[network.id]]">Статус: <strong>{{statusesByNetwork[network.id] === 1 ? "Готово" : (statusesByNetwork[network.id] === -1 ? "Не готово" : errorsByNetwork[network.id] ) }}</strong></span>
                <a class="crossposts-editor__network__link" v-if="crosspostsByNetwork[network.id] && statusesByNetwork[network.id] === 1" :href="crosspostsByNetwork[network.id].link" target="_blank">Просмотреть пост</a>
                <div class="crossposts-editor__network__buttons">
                    <a class="button button--light" @click="makePost(network.id)">{{statusesByNetwork[network.id] === 1 ? "Обновить пост" : "Сделать пост" }}</a>
                </div>
            </div>
        </div>
    </div>
</template>
<style lang="scss">
    .crossposts-editor {
        background: #efefef;
        color: #555;
        margin: 0 0 1em;
        &__title {
            font-size: 1.125em;
            font-weight: bold;
            padding: .75em;
        }

        &__network {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            padding: .5em .85em;
            border-top: 1px solid #ccc;
            &__status {
                &--error {
                    strong {
                        color: #f00;
                    }
                }
                &--success {
                    strong {
                        color: blue;
                    }
                }
            }
            &__name {
                font-size: 1.125em;
                font-weight: 500;
                margin: 0 .5em 0 0;
            }

            &__link {
                margin: 0 1em;
            }
            &__buttons {
                white-space: nowrap;
                flex: 1;
                text-align: right;
            }
        }
    }
</style>
<script>
    export default {
        methods: {
            makePost(networkId) {
                this.$set(this.statusesByNetwork, networkId, -2);
                $.post('/articles/crosspost', {article_id: this.article.id, network_id: networkId}).done((res) => {
                   if (res.status) {
                       this.$set(this.statusesByNetwork, networkId, 1);
                       let crosspost = res.data.crosspost;
                       crosspost.link = res.data.link;
                       this.$set(this.crosspostsByNetwork, networkId, crosspost);
                    } else {
                       this.$set(this.errorsByNetwork, networkId, res.text);
                       this.$set(this.statusesByNetwork, networkId, 0);
                    }
                }).catch(xhr => {
                    let error = xhr.responseJSON;
                    let text = error.message || "Неизвестная ошибка";
                    this.$set(this.errorsByNetwork, networkId, text);
                    this.$set(this.statusesByNetwork, networkId, 0);
                })
            }
        },
        data() {
            return {
                statusClasses: {
                    '0': 'error',
                    '1': 'success'
                },
                errorsByNetwork: {},
                crosspostsByNetwork: {},
                statusesByNetwork: {}
            }
        },
        mounted() {
            this.crossposts.forEach(crosspost => {
                this.$set(this.crosspostsByNetwork, crosspost.network, crosspost);
            });
            this.networks.forEach(network => {
                this.$set(this.statusesByNetwork, network.id, this.crosspostsByNetwork[network.id] ? 1 : -1);
            });
            console.log(this.statusesByNetwork);
        },
        props: ['crossposts', 'article', 'networks']
    }
</script>