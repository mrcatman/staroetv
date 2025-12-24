import {RecordsUploadData} from "../../composables/record-form";
import {capitalizeFirstLetter} from "../strings";

const generateDate = (data: RecordsUploadData) => {
    if (data.date.range && data.date.year_start > 0) {
        if (data.date.year_end > 0) {
            return `${data.date.year_start}-${data.date.year_end}`;
        }
        return data.date.year_start;
    }
    let date = [
        (data.date.day > 0 ? data.date.day : ''),
        (data.date.month > 0 ? data.date.month.toString().padStart(2, '0') : ''),
        (data.date.year > 0 ? data.date.year : '')
    ].join('.');

    if (!date.trim().length) {
        date = "неизвестная дата";
    }

    return date;
}

export const generateTitle = (data: RecordsUploadData) => {
    let title;
    if (data.type === 'advertising') {
        title = data.advertising.brand;
        if (!data.date.year_start || !data.date.year_end || data.date.year_start === data.date.year_end) {
            title += ` (${data.date.year})`;
        } else {
            title += ` (${data.date.year_start}-${data.date.year_end})`;
        }
        if (data.short_description?.length) {
            title += ` ${data.short_description}`;
        }
        return title;
    }

    let channel = !data.channel.unknown ? data.channel.name : 'Неизвестный канал';
    let program = !data.program.unknown ? data.program.name : 'Неизвестная программа';

    const date = generateDate(data);

    title = `${program} (${channel}, ${date} ${data.short_description})`.trim();
    return capitalizeFirstLetter(title);
}

export const generateInterprogramTitle = (data: RecordsUploadData, category?: Models.Genre) => {
    if (!category) {
        return generateTitle(data);
    }

    let title = category.name!;

    let channelAndYearText = `(${data.channel.name}, ${generateDate(data)})`;

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
