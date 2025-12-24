<template>
    <div class="inputs-line" v-if="!range">
        <div class="inputs-line__item">
            <div class="inputs-line__item__title">День</div>
            <select2 ref="day" name="day" theme="default" :options="dayOptions" v-model="date.day"
                     @change="onDayChange"/>
        </div>
        <div class="inputs-line__item" v-if="!range">
            <div class="inputs-line__item__title">Месяц</div>
            <select2 ref="month" name="month" theme="default" :options="monthOptions" v-model="date.month"
                     :customOptions="monthFilter"
                     @change="onMonthChange"></select2>
        </div>
        <div class="inputs-line__item">
            <div class="inputs-line__item__title">Год</div>
            <select2 ref="year" name="year" theme="default" :options="yearOptions" v-model="date.year"
                     @change="onYearChange"/>
        </div>
    </div>
    <div class="inputs-line" v-else>
        <div class="inputs-line__item">
            <div class="inputs-line__item__title">Год начала показа</div>
            <select2 theme="default" :options="yearOptions" v-model="date.year_start"></select2>
        </div>
        <div class="inputs-line__item">
            <div class="inputs-line__item__title">Год окончания показа</div>
            <select2 theme="default" :options="yearOptions" v-model="date.year_end"></select2>
        </div>
    </div>
</template>
<script lang="ts" setup>
import {monthNames} from "@/consts.js";
import {getDaysInMonth, getYearOptions} from "@/utils/dates";
import {RecordsUploadDate} from "@/composables/record-form";
import {computed, useTemplateRef} from "vue";

const date = defineModel<RecordsUploadDate>('date', {
    default: {
        year: -1,
        month: -1,
        day: -1
    }
})

defineProps<{
    range?: boolean
}>();
const monthRef = useTemplateRef('month');
const yearRef = useTemplateRef('year');

const yearOptions = getYearOptions();
const dayOptions = computed(() => {
    const days = [{id: -1, text: 'Неизвестно'}];
    const daysInMonthNumber = date.value.month > 0 ? getDaysInMonth(date.value.year)[date.value.month - 1] : 31;
    for (let i = 1; i <= daysInMonthNumber; i++) {
        days.push({id: i, text: i.toString()});
    }
    return days;
});

const monthOptions = computed(() => {
    const months = [{id: -1, text: 'Неизвестно'}];
    for (let i = 1; i <= 12; i++) {
        months.push({id: i, text: monthNames[i - 1]});
    }
    return months;
});

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
    // @ts-ignore
    $(monthRef.value.select2).select2('open');
}

const onMonthChange = () => {
    // @ts-ignore
    $(yearRef.value.select2).select2('open');
}

const onYearChange = () => {
    date.value.year_start = date.value.year;
}

</script>
