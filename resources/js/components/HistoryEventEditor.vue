<template>
    <div class="history-event-editor">
        <input type="hidden" name="records" v-model="recordsJson">
        <div class="history-event-editor__top">
            <div class="row">
                <div class="col">
                    <div class="input-container input-container--vertical">
                        <label class="input-container__label">Дата начала события</label>
                        <div class="input-container__inner">
                            <div class="input-container__element-outer">
                                <Datepicker name="date" v-model="event.date"></Datepicker>
                                <div class="input-container__description">(если событие привязано ко времени, например это подборка выпусков новостей по теме)</div>
                            </div>
                            <span class="input-container__message"></span>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="input-container input-container--vertical">
                        <label class="input-container__label">Дата конца события</label>
                        <div class="input-container__inner">
                            <div class="input-container__element-outer">
                                <Datepicker name="date_end" can_be_now="true" v-model="event.date_end"></Datepicker>
                                <div class="input-container__description">(если событие привязано ко времени и длится больше 1 дня)</div>
                            </div>
                            <span class="input-container__message"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="history-event-editor__block" v-for="(block, $index) in blocks" :key="$index">
            <div class="history-event-editor__block__top">
                <div class="row">
                    <div class="col">
                        <div class="input-container input-container--vertical">
                            <label class="input-container__label">Описание блока записей</label>
                            <div class="input-container__inner">
                                <textarea v-model="block.description" class="input" ></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="col col--button">
                        <div class="history-event-editor__delete" @click="blocks.splice($index, 1)">
                            <a class="button">
                                <i class="fa fa-trash"></i>
                                Удалить блок
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="history-event-editor__records-picker">
                <records-list-picker @selected="(records) => setRecords(block, records)" :hide-selected-button="true" :descriptions="true" :list="block.records" :meta="{}" :manual="true" :select="{}"/>

            </div>
        </div>
        <div class="history-event-editor__add-block" @click="addBlock()">
            <a class="button">Добавить еще блок записей</a>
        </div>
    </div>
</template>
<style lang="scss">
    .history-event-editor {
        &__block {
            padding: 0 0 1em;
            border-bottom: 1px solid var(--border-color);
            margin: 1em;
        }
        &__top {
            padding: 0 1em;
        }

        .records-list-picker {
            margin: 0;
            border-top: 1px solid var(--border-color-dark);
        }

        .records-list-picker .box__heading {
            font-size: 1.25em;
        }
    }
</style>
<script>
    import Datepicker from './datepicker/components/Datepicker';
    export default  {
        computed: {
            recordsJson() {
                let data = JSON.parse(JSON.stringify(this.blocks));
                data.forEach(block => {
                    block.records = block.records.map(record => {
                        return {id: record.id, description: record.block_description};
                    });
                });
                return JSON.stringify(data);
            }
        },
        methods: {
            setRecords(block, records) {
                block.records = records;
            },
            addBlock() {
                this.blocks.push({
                    description: '',
                    records: []
                })
            }
        },
        data() {
            return {
                event: this.data ? this.data : {},
                blocks: this.data ? this.data.blocks : [
                    {
                        description: '',
                        records: []
                    }
                ]
            }
        },
        props: ['data'],
        components: {
            Datepicker
        }
    }
</script>
