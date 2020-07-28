<template>
    <div class="categories-manager">
        <div class="admin-panel__heading-container">
            <div class="admin-panel__heading">Категории</div>
            <div class="tabs">
                <a class="tab" :class="{'tab--active': type === 'programs'}" @click="type = 'programs'">Программы</a>
                <a class="tab" :class="{'tab--active': type === 'interprogram'}" @click="type = 'interprogram'">Оформление</a>
                <a class="tab" :class="{'tab--active': type === 'advertising'}" @click="type = 'advertising'">Реклама</a>
                <a class="tab" :class="{'tab--active': type === 'videos_other'}" @click="type = 'videos_other'">Доп. категории видео</a>
           </div>
        </div>
        <div class="admin-panel__main-content">
            <div class="form">
                <div class="form__preloader" v-if="loading"></div>
                <table class="admin-panel__table">
                    <thead>
                        <tr>
                            <td>Название</td>
                            <td>URL</td>
                            <td v-show="type == 'interprogram'">Паттерн автомат. названия</td>
                            <td></td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(category, $index) in categoriesList" v-show="category.type === type" :key="$index">
                            <td>
                                <input class="input" v-model="categoriesList[$index].name"/>
                            </td>
                            <td>
                                <input class="input" v-model="categoriesList[$index].url"/>
                            </td>
                            <td v-show="type == 'interprogram'">
                                <input class="input" v-model="categoriesList[$index].name_pattern"/>
                            </td>
                            <td>
                                <a @click="categoriesList.splice($index, 1)" class="button button--light">Удалить</a>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <a class="button button--light" @click="addCategory()">Добавить еще категорию</a>
                <br><br>
                <div class="form__bottom">
                    <a @click="saveCategories()" class="button button--light">Сохранить</a>
                    <response :light="true" :data="response"/>
                </div>
            </div>
        </div>
    </div>
</template>
<style lang="scss">
    .categories-manager {
        .input {

        }
    }
</style>
<script>
    import Response from '../Response';
    export default {
        computed: {

        },
        methods: {
            saveCategories() {
                this.loading = true;
                $.post('/admin/categories', {categories: this.categoriesList}).done(res => {
                    this.loading = false;
                    this.response = res;
                    if (res.status) {
                        this.categoriesList = res.data.categories;
                    }
                }).fail((xhr) => {
                    this.loading = false;
                    let error = xhr.responseJSON;
                    this.response = {status: 0, text: error.message === "" ? "Неизвестная ошибка" : error.message};
                })
            },
            addCategory() {
                this.categoriesList.push({
                    name: '',
                    url: '',
                    type: this.type
                });
            }
        },
        props: {
            categories: {
                type: Array,
                required: true,
            },
        },
        data() {
            return {
                type: 'programs',
                response: null,
                loading: false,
                categoriesList: this.categories,
            }
        },
        mounted() {

        },
        components: {
            Response,
        }
    }
</script>
