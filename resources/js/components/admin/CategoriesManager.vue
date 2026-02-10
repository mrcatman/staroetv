<template>
    <div class="categories-manager">
        <div class="tabs">
            <a class="tab" :class="{'tab--active': type === 'programs'}" @click="type = 'programs'">Программы</a>
            <a class="tab" :class="{'tab--active': type === 'interprogram'}"
               @click="type = 'interprogram'">Оформление</a>
            <a class="tab" :class="{'tab--active': type === 'advertising'}" @click="type = 'advertising'">Реклама</a>
            <a class="tab" :class="{'tab--active': type === 'videos_other'}" @click="type = 'videos_other'">Доп.
                категории видео</a>
        </div>

        <div class="categories-manager__main">
            <div class="form">
                <div class="form__preloader" v-if="loading"></div>
                <table class="admin-panel__table">
                    <thead>
                    <tr>
                        <td>Название</td>
                        <td>URL</td>
                        <td v-show="type === 'interprogram'">Паттерн автомат. названия</td>
                        <td></td>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="(category, index) in categoriesList" v-show="category.type === type" :key="index">
                        <td>
                            <input class="input" v-model="categoriesList[index].name"/>
                        </td>
                        <td>
                            <input class="input" v-model="categoriesList[index].url"/>
                        </td>
                        <td v-show="type === 'interprogram'">
                            <input class="input" v-model="categoriesList[index].name_pattern"/>
                        </td>
                        <td>
                            <a @click="categoriesList.splice(index, 1)" class="button button--light">Удалить</a>
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
    &__main {
        margin-top: 1em;
    }
}
</style>
<script setup lang="ts">
import { ref } from 'vue';
import Response from '../Response.vue';
import { getErrorMessage } from "@/utils/errors";

const props = defineProps<{
    categories: Models.Genre[];
}>();

const type = ref<string>('programs');
const response = ref<Forms.Response | null>(null);
const loading = ref<boolean>(false);
const categoriesList = ref<Models.Genre[]>([...props.categories]);

const saveCategories = () => {
    loading.value = true;
    $.post(route('admin.genres.save'), {categories: categoriesList.value}).done(res => {
        loading.value = false;
        response.value = res;
        if (res.status) {
            categoriesList.value = res.data.categories;
        }
    }).fail((err) => {
        loading.value = false;
        response.value = {status: 0, text: getErrorMessage(err)};
    })
};

const addCategory = () => {
    categoriesList.value.push({
        name: '',
        url: '',
        type: type.value
    });
};
</script>
