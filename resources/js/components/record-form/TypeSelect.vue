<template>
    <div class="radio-buttons radio-buttons--tabs radio-buttons--tabs-wide">
        <label class="radio-button radio-button--tabs">
            <input type="radio" v-model="type" value="programs"/>
            <div class="radio-button--tabs__variant">Передача</div>
        </label>
        <label class="radio-button radio-button--tabs">
            <input type="radio" v-model="type" value="interprogram"/>
            <div class="radio-button--tabs__variant">Заставка канала, анонс и т.д.</div>
        </label>
        <label class="radio-button radio-button--tabs">
            <input type="radio" v-model="type" value="advertising"/>
            <div class="radio-button--tabs__variant">Рекламный ролик</div>
        </label>
        <label class="radio-button radio-button--tabs">
            <input type="radio" v-model="type" value="program-design"/>
            <div class="radio-button--tabs__variant">Заставка передачи</div>
        </label>
        <label class="radio-button radio-button--tabs">
            <input type="radio" v-model="type" value="other"/>
            <div class="radio-button--tabs__variant">Прочее</div>
        </label>
        <!--
        <label class="radio-button radio-button--tabs">
            <input type="radio" v-model="data.type" name="type" value="clip"/>
            <div class="radio-button--tabs__variant">Клип</div>
        </label>
        -->
    </div>
</template>
<script lang="ts" setup>
const type = defineModel<Records.Type>();
</script>
