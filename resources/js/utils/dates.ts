import { monthNames, monthNamesToNumbers, monthNamesToNumbersGenitive } from "@/consts";

export const parseDate = (dateString: string): { day: number, month: number, year: number } => {
    let year, month, day: number;
    if (dateString !== "") {
        dateString = dateString.replace('~','').split(";")[0].replace("–", "-");

        const splittedMin = dateString.split("-");
        if (splittedMin.length === 2) {
            year = parseInt(splittedMin[0]);
            dateString = splittedMin[1];
        }
        if (dateString.split(".").length !== 3 && dateString.split(" ").length !== 3) {
            const splitted = dateString.split(" ");
            if (splitted.length === 1) {
                year = parseInt(splitted[0]);
            } else if (splitted.length === 2) {
                year = parseInt(splitted[1]);
                month = splitted[0].toLowerCase();
                if (monthNamesToNumbers[month]) {
                    month = monthNamesToNumbers[month];
                }
            }
        } else {
            if (dateString.split(".").length !== 3) {
                const splitted = dateString.split(" ");

                day = parseInt(splitted[0]);
                if (monthNamesToNumbersGenitive[splitted[1]]) {
                    month = monthNamesToNumbersGenitive[splitted[1]];
                }
                year = parseInt(splitted[2]);
            } else {
                const dateStringFixed = dateString.trim().replace('/[^0-9.]+/', '').split(".").map(i => parseInt(i));
                day = dateStringFixed[0];
                month = dateStringFixed[1];
                year = dateStringFixed[2];
            }
        }
    }
    return {
        day, month, year
    }
}

export const getYearOptions = () => {
    const years = [{id: -1, text: 'Неизвестно'}];
    for (let i = 1950; i <= 2010; i++) {
        years.push({id: i, text: i.toString()});
    }
    return years;
}

export const getMonthOptions = () => {
    const months = [{id: -1, text: 'Неизвестно'}];
    for (let i = 1; i <= 12; i++) {
        months.push({id: i, text: monthNames[i - 1]});
    }
    return months;
}

export const getDaysInMonth = (year: number) => {
    const isLeapYear = year > 0 && ((year % 4 === 0) && (year % 100 !== 0)) || (year % 400 === 0);
    return [
        31, isLeapYear ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31
    ];
}

export const defaultDate = (): Common.Date  => {
    return {
        year: -1,
        month: -1,
        day: -1,
        range: false,
        year_start: -1,
        month_start: -1,
        day_start: -1,
        year_end: -1,
        month_end: -1,
        day_end: -1,
    }
}
