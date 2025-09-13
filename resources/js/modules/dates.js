import {monthNamesToNumbers, monthNamesToNumbersGenitive} from "@/consts.js";

export const parseDate = (dateString) => {
    let year, month, day;
    if (dateString !== "") {
        dateString = dateString.split(";")[0].replace("–", "-");

        const splittedMin = date.split("-");
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

                day = splitted[0];
                if (monthNamesToNumbersGenitive[splitted[1]]) {
                    month = monthNamesToNumbersGenitive[splitted[1]];
                }
                year = splitted[2];
            } else {
                dateString = dateString.trim().replace('/[^0-9.]+/', '').split(".");
                day = dateString[0];
                month = dateString[1];
                year = dateString[2];
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

export const getDaysInMonth = (year) => {
    const isLeapYear = year > 0 && ((year % 4 === 0) && (year % 100 !== 0)) || (year % 400 === 0);
    return [
        31, isLeapYear ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31
    ];
}
