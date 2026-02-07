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
                <tr v-for="(smile, index) in smilesList" :key="index">
                    <td>
                        <picture-uploader :small="true" :light="true" v-model="smile.picture"/>
                    </td>
                    <td>
                        <input class="input" v-model="smilesList[index].text"/>
                    </td>
                    <td>
                        <input type="checkbox" v-model="smilesList[index].show_in_panel"/>
                    </td>
                    <td>
                        <a @click="smilesList.splice(index, 1)" class="button button--light">Удалить</a>
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

<script setup lang="ts">
import { ref } from 'vue';
import PictureUploader from '../PictureUploader.vue';
import Response from '../Response.vue';

const props = defineProps<{
    smiles: Models.Smile[];
}>();

const response = ref<Forms.Response | null>(null);
const loading = ref<boolean>(false);
const smilesList = ref<Models.Smile[]>([...props.smiles]);

const saveSmiles = () => {
    loading.value = true;
    $.post(route('admin.smiles.save'), {smiles: smilesList.value}).done(res => {
        loading.value = false;
        response.value = res;
        if (res.status) {
            smilesList.value = res.data.smiles;
        }
    }).fail((xhr) => {
        loading.value = false;
        const error = xhr.responseJSON;
        response.value = {status: 0, text: error.message === "" ? "Неизвестная ошибка" : error.message};
    })
};

const addSmile = () => {
    smilesList.value.push({
        id: 0,
        picture_id: 0,
        text: '',
        show_in_panel: false
    } as Models.Smile);
};
</script>
