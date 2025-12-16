import {monthNamesToNumbers, monthNamesToNumbersGenitive} from "@/consts.js";

export const parseDate = (dateString: string): { day: number, month: number, year: number } => {
    let year, month, day: number;
    if (dateString !== "") {
        dateString = dateString.split(";")[0].replace("–", "-");

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
    const years = [{id: null, text: 'Неизвестно'}];
    for (let i = 1950; i <= 2010; i++) {
        years.push({id: i, text: i.toString()});
    }
    return years;
}

export const getDaysInMonth = (year: number) => {
    const isLeapYear = year > 0 && ((year % 4 === 0) && (year % 100 !== 0)) || (year % 400 === 0);
    return [
        31, isLeapYear ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31
    ];
}
