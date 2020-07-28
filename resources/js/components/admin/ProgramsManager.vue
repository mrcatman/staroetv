<template>
    <div class="programs-manager" @mousedown="mousedown = true"  @drop="mousedown = false" @dragend="mousedown = false"  @mouseup="mousedown = false">


        <modal ref="mergeModal" title="Объединение программ" :loading="mergePanel.loading">
            <div class="input-container" v-if="!mergePanel.data.is_interprogram">
                <label class="input-container__label">Программа</label>
                <div class="input-container__inner">
                    <select2 theme="default" :options="mergeOptions" v-model="mergePanel.data.merged_id"/>
                </div>
            </div>
            <label class="input-container input-container--checkbox">
                <input type="checkbox" v-model="mergePanel.data.is_interprogram">
                <div class="input-container--checkbox__element"></div>
                <div class="input-container__label">Переместить видео в раздел с межпрограммным оформлением</div>
            </label>
            <div class="form__bottom form__bottom--with-margin">
                <a @click="mergePrograms()" class="button button--light">Выбрать</a>
                <Response :light="true" :data="mergePanel.response"/>
            </div>
        </modal>

        <div class="programs-manager__form">
            <div class="form">
                <div class="form__preloader" v-if="loading"></div>
                <div class="programs-manager__cols">
                    <div class="programs-manager__col">
                        <draggable group="programs" key="without_genre" v-model="withoutGenreList" class="programs-manager__items">
                            <div class="programs-manager__item"  v-for="program in withoutGenreList" :key="program.name">
                                <span class="programs-manager__item__name">{{program.name}}</span>
                                <div class="programs-manager__item__actions">
                                    <a :href="'/programs/'+(program.url ? program.url : program.id)" target="_blank" class="programs-manager__item__action">На страницу</a>
                                    <a @click="merge(program)" class="programs-manager__item__action">Объединить...</a>
                                </div>
                            </div>
                        </draggable>
                    </div>
                    <div class="programs-manager__col">
                        <!--
                        <div class="programs-manager__tabs">
                            <div class="tabs tabs--full-size">
                                <a @mouseover="onMouseEnterGenreTab(genre)" class="tab" v-for="(genre, $index) in genres" :key="$index" :class="{'tab--active': genre.id === selectedGenreId}" @click="selectedGenreId = genre.id">{{genre.name}}</a>
                            </div>
                        </div>
                        -->
                        <div v-for="(genre, $index) in genres" :key="genre.id">
                            <h3>{{genre.name}}</h3>
                            <draggable group="programs" :key="'genre_'+genre.id" v-model="programsByGenre[genre.id]" class="programs-manager__items">
                                <div class="programs-manager__item"  v-for="program in programsByGenre[genre.id]" :key="program.name">
                                    <span class="programs-manager__item__name">{{program.name}}</span>
                                    <div class="programs-manager__item__actions">
                                        <a :href="'/programs/'+(program.url ? program.url : program.id)" target="_blank" class="programs-manager__item__action">На страницу</a>
                                        <a @click="merge(program)" class="programs-manager__item__action">Объединить...</a>
                                    </div>
                                </div>
                            </draggable>
                        </div>

                    </div>
                </div>
                <br>
                <div class="form__bottom">
                    <a @click="saveOrder()" class="button button--light">Сохранить</a>
                    <response :light="true" :data="response"/>
                </div>
            </div>
        </div>
    </div>
</template>
<style lang="scss">
    .programs-manager {
        padding: 1em;
        &__cols {
            display: flex;
        }

        &__col {
            flex: 1;
            margin: 0 1em 0 0;
            display: flex;
            flex-direction: column;
            max-height: 75vh;
            overflow: auto;
        }

        &__item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .5em;
            margin: 0 0 .25em;
            background: var(--bg-darker-2);
            border: 1px solid var(--border-color);
            font-weight: 400;
            &__action {
                margin: 0 0 0 .5em;
                font-size: .875em;
                color: #999;
                text-decoration: underline;
                cursor: pointer;
            }
        }
        &__tabs {
            margin: 0 0 .5em;
        }
        &__items {
            flex: 1;
        }
    }
</style>
<script>
    import draggable from 'vuedraggable'
    import Response from '../Response'
    import Modal from '../Modal'

    export default {
        beforeDestroy() {

        },
        watch: {

        },
        computed: {
            mergeOptions() {
                let programs = this.mergePanel.program ? this.programsList.filter(program => program.id !== this.mergePanel.program.id) : this.programsList;
                return programs.map(program => {
                    return {id: program.id, text: program.name};
                })
            }
        },
        methods: {
            mergePrograms() {
                this.mergePanel.loading = true;
                $.post('/programs/merge', {
                    original_id: this.mergePanel.program.id,
                    merged_id: this.mergePanel.data.merged_id,
                    is_interprogram: this.mergePanel.data.is_interprogram
                }).done(res => {
                    this.mergePanel.loading = false;
                    this.mergePanel.response = res;
                    if (res.status) {
                        this.$refs.mergeModal.hide();
                        this.programsList = this.programsList.filter(program => program.id !== this.mergePanel.program.id);
                        let genreId = this.mergePanel.program.genre_id;
                        if (genreId && genreId > 0) {
                            this.programsByGenre[genreId] = this.programsByGenre[genreId].filter(program => program.id !== this.mergePanel.program.id);
                        } else {
                            this.withoutGenreList = this.withoutGenreList.filter(program => program.id !== this.mergePanel.program.id);
                        }
                    }
                }).fail((xhr) => {
                    this.mergePanel.loading = false;
                    let error = xhr.responseJSON;
                    this.mergePanel.response = {status: 0, text: error.message === "" ? "Неизвестная ошибка" : error.message};
                })
            },
            merge(program) {
                this.mergePanel.program = program;
                this.$refs.mergeModal.show();
            },
            onMouseEnterGenreTab(genre) {
                if (this.mousedown) {
                    this.selectedGenreId = genre.id;
                }
            },
            saveOrder() {
                this.loading = true;
                let order = {};
                Object.keys(this.programsByGenre).forEach(genreId => {
                    order[genreId] = this.programsByGenre[genreId].map(program => program.id);
                })
                order[-1] = this.withoutGenreList.map(program => program.id);
                $.post(`/channels/${this.channel.url}/programs/edit`, {order}).done(res => {
                    this.loading = false;
                    this.response = res;
                }).fail((xhr) => {
                    this.loading = false;
                    let error = xhr.responseJSON;
                    this.response = {status: 0, text: error.message === "" ? "Неизвестная ошибка" : error.message};
                })
            },
        },
        props: {
            channel: {
                type: Object,
                required: true
            },
            genres: {
                type: Array,
                required: true,
            },
            programs: {
                type: Array,
                required: true,
            },
        },
        data() {
            return {
                mousedown: false,
                selectedGenreId: this.genres[0].id,
                programsList: this.programs,
                response: null,
                loading: false,
                withoutGenreList: [],
                programsByGenre: {},
                mergePanel: {
                    data: {
                        merged_id: -1,
                        is_interprogram: false,
                    },
                    program: null,
                    loading: false,
                    response: null,
                }
            }
        },
        mounted() {
            this.withoutGenreList = this.programsList.filter(program => !program.genre_id);
            this.genres.forEach(genre => {
                this.$set(this.programsByGenre, genre.id, this.programsList.filter(program => program.genre_id === genre.id));
            })
        },
        components: {
            Modal,
            Response,
            draggable
        }
    }
</script>
