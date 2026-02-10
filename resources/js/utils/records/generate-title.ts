import { RecordsUploadData } from "../../composables/record-form";
import { capitalizeFirstLetter } from "../strings";
import { monthNames } from "@/consts";

const generateDate = (data: RecordsUploadData) => {
    if (data.date.range && data.date.year_start > 0) {
        if (data.date.year_end > 0) {
            return `${data.date.year_start}-${data.date.year_end}`;
        }
        return data.date.year_start > 0? data.date.year_start.toString() : 'неизвестная дата';
    }

    let date: string;
    if (data.date.day > 0 && data.date.month > 0 && data.date.year > 0 ) {
        date = [
            (data.date.day.toString()),
            (data.date.month.toString().padStart(2, '0')),
            (data.date.year.toString())
        ].join('.');
    } else if (data.date.month > 0) {
        date = `${monthNames[data.date.month - 1].toLocaleLowerCase()} ${data.date.year}`;
    } else if (data.date.year > 0) {
        date = data.date.year.toString();
    }

    if (!date?.trim().length) {
        date = "неизвестная дата";
    }

    return date;
}

export const generateTitle = (data: RecordsUploadData, interprogram?: boolean) => {
    let title;
    if (data.type === 'advertising') {
        title = data.advertising.brand;
        if (!data.date.year_start || !data.date.year_end || data.date.year_start === data.date.year_end || (data.date.year_start > 0 && data.date.year_end <= 0)) {
            title += ` (${data.date.year > 0 ? data.date.year : 'неизвестная дата'})`;
        } else {
            title += ` (${data.date.year_start >0 ? `${data.date.year_start}-${data.date.year_end}` : 'неизвестная дата'})`;
        }
        if (data.short_description?.length) {
            title += ` ${data.short_description}`;
        }
        return title;
    }

    const channel = data.channel && !data.channel.unknown && data.channel.name?.length ? data.channel.name : 'неизвестный канал';
    let program = data.program && !data.program.unknown && data.program.name?.length ? data.program.name : (interprogram ? '?' : 'неизвестная программа');

    if (data.type === 'program-design') {
        program = `заставка программы "${program}"`;
    }

    const date = generateDate(data);

    title = `${program} (${channel}, ${date}) ${data.short_description}`.trim();
    return capitalizeFirstLetter(title);
}

export const generateInterprogramTitle = (data: RecordsUploadData, category?: Models.Genre) => {
    if (!category) {
        return generateTitle(data, true);
    }

    let title = category.name!;

    let channelAndYearText = `(${data.channel?.name || 'Неизвестный канал'}, ${generateDate(data)})`;

    if (category.name_pattern) {
        title = category.name_pattern.replace(/[\[{(].*?[\]})]/g, (property) => {
            property = property.replace('{', '');
            property = property.replace('}', '');
            if (property === 'data') {
                return channelAndYearText;
            } else if (property === "short_description") {
                if (!data.short_description.length) {
                    return '';
                }
                return data.short_description;
            } else if (property === 'program_name') {
                return data.program.name;
            } else {
                return data[property];
            }
        });
    } else {
        title += ` ${channelAndYearText}`;
        if (data.short_description?.length) {
            title += ` ${data.short_description}`;
        }
    }
    return capitalizeFirstLetter(title.trim());
}
