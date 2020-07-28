<template>
    <div class="questionnaire__editor">
        <input type="hidden" name="questionnaire_data" :value="questionnaireJson" />
        <div class="row questionnaire__editor__top">
            <div class="col">
                <div class="input-container input-container--vertical">
                    <label class="input-container__label">Название опроса</label>
                    <div class="input-container__inner">
                        <input class="input" v-model="questionnaire.title"/>
                        <span class="input-container__message"></span>
                    </div>
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
            background: var(--bg-darker);
            width: calc(100% - 2em);
            color: #333;
        }
        &__bottom {
            background: var(--bg-darker-2);
            padding: 1em;
        }
        &__row {
            border-bottom: 1px solid var(--border-color);
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
            flex: 1;
            background: var(--box-color);
            color: var(--text-color);
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
