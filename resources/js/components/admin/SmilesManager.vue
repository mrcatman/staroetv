<template>
    <div class="smiles-manager">
        <div class="admin-panel__heading-container">
            <div class="admin-panel__heading">Смайлы</div>
        </div>
        <div class="admin-panel__main-content">
            <div class="form">
                <div class="form__preloader" v-if="loading"></div>
                <table class="admin-panel__table">
                    <thead>
                        <tr>
                            <td>Картинка</td>
                            <td>Текст</td>
                            <td>Показывать в главном окне</td>
                            <td></td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(smile, $index) in smilesList" :key="$index">
                            <td>
                                <PictureUploader :small="true" :light="true" v-model="smile.picture_id" :data="smile.picture"/>
                            </td>
                            <td>
                                <input class="input" v-model="smilesList[$index].text"/>
                            </td>
                            <td>
                                <input type="checkbox" v-model="smilesList[$index].show_in_panel"/>
                            </td>
                            <td>
                                <a @click="smilesList.splice($index, 1)" class="button button--light">Удалить</a>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <a class="button button--light" @click="addSmile()">Добавить еще смайл</a>
                <br><br>
                <div class="form__bottom">
                    <a @click="saveSmiles()" class="button button--light">Сохранить</a>
                    <response :light="true" :data="response"/>
                </div>
            </div>
        </div>
    </div>
</template>
<style lang="scss">
    .smiles-manager {

    }
</style>
<script>
    import PictureUploader from '../PictureUploader';
    import Response from '../Response';

    export default {
        computed: {

        },
        methods: {
            saveSmiles() {
                this.loading = true;
                $.post('/admin/smiles', {smiles: this.smilesList}).done(res => {
                    this.loading = false;
                    this.response = res;
                    if (res.status) {
                        this.smilesList = res.data.smiles;
                    }
                }).fail((xhr) => {
                    this.loading = false;
                    let error = xhr.responseJSON;
                    this.response = {status: 0, text: error.message === "" ? "Неизвестная ошибка" : error.message};
                })
            },
            addSmile() {
                this.smilesList.push({
                    text: '',
                    show_in_panel: false
                })
            }
        },
        props: {
            smiles: {
                type: Array,
                required: true,
            },
        },
        data() {
            return {
                response: null,
                loading: false,
                smilesList: this.smiles,
            }
        },
        mounted() {

        },
        components: {
            PictureUploader,
            Response,
        }
    }
</script>
