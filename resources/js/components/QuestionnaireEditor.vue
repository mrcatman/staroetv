<template>
    <div class="questionnaire__editor">
        <input type="hidden" name="questionnaire_data" :value="questionnaireJson" />
        <div class="row questionnaire__editor__top">
            <div class="col">
                <label class="input-container__label">Название опроса</label>
                <div class="input-container__inner">
                    <input class="input" v-model="questionnaire.title"/>
                    <span class="input-container__message"></span>
                </div>
            </div>
            <div class="col">
                <label class="input-container input-container--checkbox">
                    <input type="checkbox" v-model="questionnaire.multiple_variants">
                    <div class="input-container--checkbox__element"></div>
                    <div class="input-container__label">Несколько вариантов ответа</div>
                </label>
            </div>
        </div>
        <div class="questionnaire__editor__inner">
            <div class="row questionnaire__editor__row" v-for="(variant, $index) in questionnaire.variants" :key="$index">
                <div class="questionnaire__editor__row__input-container">
                    <div class="input-container input-container--vertical">
                        <div class="input-container__inner">
                            <input v-model="variant.title" class="input"/>
                        </div>
                    </div>
                </div>
                <div class="questionnaire__editor__row__button-container">
                    <a class="button button--light" @click="deleteItem($index)">Удалить</a>
                </div>
            </div>
        </div>
        <div class="questionnaire__editor__bottom">
            <a class="button button--light" @click="addItem()">Добавить еще пункт</a>
        </div>

    </div>
</template>
<style lang="scss">
    .questionnaire__editor {
        width: 100%;
        &__top {
            padding: 1em;
            background: #f4f4f4;
            width: calc(100% - 2em);
            color: #333;
        }
        &__bottom {
            background: #ddd;
            padding: 1em;
            border-top: 1px solid #999;
        }
        &__row {
            border-bottom: 1px solid #ccc;
            &__input-container {
                flex: 1;
                margin: .5em 0 0;
            }
            &__button-container {
                margin: .5em 0 0 1em;
            }
        }

        &__inner {
            padding: 0 1em;
            background: linear-gradient(rgba(55, 52, 47, 0.67), rgba(0, 0, 0, 0.5));
            flex: 1;
            background: #eee;
            color: #000;
            font-family: "Roboto", sans-serif;
        }
    }
</style>
<script>

    export default {
        computed: {
            questionnaireJson() {
                return JSON.stringify(this.questionnaire)
            }
        },
        methods: {
            deleteItem(index) {
                this.questionnaire.variants.splice(index, 1);
                //this.$forceUpdate();
            },
            addItem() {
                let data = JSON.parse(JSON.stringify({
                    title: ""
                }));
                this.questionnaire.variants.push(data);
            }
        },
        props: {
            data: {
                type: Object,
            }
        },
        data() {
            return {
                questionnaire: this.data || {title: '', variants: []}
            }
        },
    }
</script>
