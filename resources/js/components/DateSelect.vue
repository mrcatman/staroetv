<template>
    <div class="inputs-line">
        <div class="inputs-line__item" v-if="!hideDayAndMonth">
            <div class="inputs-line__item__title">День</div>
            <select2 ref="day" name="day" theme="default" :options="dayOptions" v-model="date.day" @change="onDayChange" />
        </div>
        <div class="inputs-line__item"  v-if="!hideDayAndMonth">
            <div class="inputs-line__item__title">Месяц</div>
            <select2 ref="month" name="month" theme="default" :options="monthOptions" v-model="date.month" @change="onMonthChange"></select2>
        </div>
        <div class="inputs-line__item">
            <div class="inputs-line__item__title">Год</div>
            <select2 ref="year" name="year" theme="default" :options="yearOptions" v-model="date.year" />
        </div>
    </div>
</template>
<script>
import { monthNames } from "@/consts.js";
import {getDaysInMonth, getYearOptions} from "@/modules/dates.js";
export default {
    props: {
        value: {
            type: Object,
            required: false,
            default: () => {
                return {
                    year: -1,
                    month: -1,
                    day: -1
                }
            }
        },
        hideDayAndMonth: {
            type: Boolean,
            required: false,
            default: false
        }
    },
    data() {
        return {
            date: this.value || {
                year: -1,
                month: -1,
                day: -1
            },
            yearOptions: getYearOptions()
        }
    },
    watch: {
        date(date) {
            this.$emit('input', date);
        },
        value (date) {
            this.date = date;
        }
    },
    computed: {
        dayOptions() {
            const days = [{id: null, text: 'Неизвестно'}];
            const daysInMonthNumber = this.date.month > 0 ? getDaysInMonth(this.date.year)[this.date.month - 1] : 31;
            for (let i = 1; i <= daysInMonthNumber; i++) {
                days.push({id: i, text: i.toString()});
            }
            return days;
        },
        monthOptions() {
            const months = [{id: null, text: 'Неизвестно'}];
            for (let i = 1; i <= 12; i++) {
                months.push({id: i, text: monthNames[i - 1]});
            }
            return months;
        },
    },
    methods: {
        onDayChange() {
            $(this.$refs.month.select2).select2('open');
        },
        onMonthChange() {
            $(this.$refs.year.select2).select2('open');
        }
    }
}
</script>
