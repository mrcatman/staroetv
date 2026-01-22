<template>
    <div class="row" v-if="!range">
        <div class="col">
            <input-container vertical label="Год" label-small>
                <select2 ref="year" name="year" theme="default" :options="yearOptions" v-model="date.year"
                         @change="onYearChange"/>
            </input-container>
        </div>
        <div class="inputs-line__item" v-if="!onlyYears">
            <input-container vertical label="Месяц" label-small>
                <select2 ref="month" name="month" theme="default" :options="monthOptions" v-model="date.month"
                         :disabled="date.year < 0"
                         :customOptions="monthFilter"
                         @change="onMonthChange"/>
            </input-container>
        </div>
        <div class="inputs-line__item" v-if="!onlyYears">
            <input-container vertical label="День" label-small>
                <select2 ref="day" name="day" theme="default" :options="dayOptions" v-model="date.day"
                         :disabled="date.month < 0"
                         @change="onDayChange"/>
            </input-container>
        </div>
    </div>
    <div class="row" :class="{'row--vertical': search && !onlyYears}" v-else>
        <div class="col">
            <input-container vertical :label="search ? 'Начальная дата' : 'Дата начала показа'" label-small>
                <div class="row">
                    <div class="col">
                        <select2 theme="default" :options="yearOptions" v-model="date.year_start"
                                 @change="onYearStartChange"/>
                    </div>
                    <div class="col" v-if="!onlyYears">
                        <select2 theme="default" :options="monthOptions" v-model="date.month_start"/>
                    </div>
                    <div class="col" v-if="!onlyYears">
                        <select2 theme="default" :options="dayOptions" v-model="date.day_start"/>
                    </div>
                </div>
            </input-container>

        </div>
        <div class="col">
            <input-container vertical :label="search ? 'Конечная дата' : 'Дата окончания показа'" label-small>
                <div class="row">
                    <div class="col">
                        <select2 theme="default" :options="yearEndOptions" v-model="date.year_end"/>
                    </div>
                    <div class="col" v-if="!onlyYears">
                        <select2 theme="default" :options="monthOptions" v-model="date.month_end"/>
                    </div>
                    <div class="col" v-if="!onlyYears">
                        <select2 theme="default" :options="dayOptions" v-model="date.day_end"/>
                    </div>
                </div>
            </input-container>

        </div>
    </div>
</template>
<script lang="ts" setup>
import { computed, ref, nextTick, useTemplateRef, watch } from "vue";

import { monthNames } from '@/consts';
import { defaultDate, getDaysInMonth, getMonthOptions, getYearOptions } from "@/utils/dates";

import Select2 from "@/components/Select2.vue";
import InputContainer from "@/components/InputContainer.vue";

const model = defineModel<Common.Date>({
    default: defaultDate()
})

const props = defineProps<{
    range?: boolean,
    onlyYears?: boolean,
    search?: boolean,
    date?: Common.Date
}>();

const date = ref<Common.Date>(props.date ?? model.value);
watch(() => date, () => {
    model.value = date.value;
}, {deep: true})

watch(() => model, () => {
    date.value = model.value;
}, {deep: true})

const dayRef = useTemplateRef<typeof Select2>('day');
const monthRef = useTemplateRef<typeof Select2>('month');
const yearRef = useTemplateRef<typeof Select2>('year');

const yearOptions = getYearOptions();
const monthOptions = getMonthOptions();

const dayOptions = computed(() => {
    const days = [{id: -1, text: 'Неизвестно'}];
    const daysInMonthNumber = date.value.month > 0 ? getDaysInMonth(date.value.year)[date.value.month - 1] : 31;
    for (let i = 1; i <= daysInMonthNumber; i++) {
        days.push({id: i, text: i.toString()});
    }
    return days;
});

const yearEndOptions = computed(() => {
    return yearOptions.filter(option => {
        return option.id >= date.value.year_start;
    })
})

const monthFilter = {
    matcher: (query, option) => {
        if (!query.term) {
            return option;
        }

        const number = parseInt(query.term);
        if (number > 0) {
            return option.text === monthNames[number - 1] ? option : null;
        }
        return option.text?.toLocaleLowerCase().includes(query.term.toLocaleLowerCase()) ? option : null;
    }
}

const onDayChange = () => {

}

const onMonthChange = async () => {
    if (date.value.day > 0) {
        return;
    }
    await nextTick();
    $(dayRef.value.select2).select2('open');
}

const onYearChange = async () => {
    date.value.year_start = date.value.year;
    if (date.value.month > 0) {
        return;
    }
    await nextTick();
    $(monthRef.value.select2).select2('open');
}

const onYearStartChange = () => {
    if (date.value.year_end <= date.value.year_start) {
        date.value.year_end = date.value.year_start;
    }
}
</script>
