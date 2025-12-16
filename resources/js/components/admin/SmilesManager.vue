<template>

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

</template>

<script>
    import PictureUploader from '../PictureUploader.vue';
    import Response from '../Response.vue';

    export default {
        methods: {
            saveSmiles() {
                this.loading = true;
                $.post(route('admin.smiles.save'), {smiles: this.smilesList}).done(res => {
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
        components: {
            PictureUploader,
            Response,
        }
    }
</script>
