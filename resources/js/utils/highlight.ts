export const highlight = (text: string, search: string, limitLength = false) => {
    if (!text) {
        return '';
    }
    text = text.replace(/<\/?[^>]+(>|$)/g, "");

    const textLength = text.length;
    const lowerCaseSearch = search.toLowerCase();

    if (search.length === 0) {
        return text;
    }
    let lowercaseText = text.toLowerCase();

    const startReplacement = '<span class="highlight">';
    const endReplacement = '</span>';
    const offsetCount = startReplacement.length + endReplacement.length;
    const maxTextSize = 250;

    let index = 0;
    let offset = 0;

    let firstMatch = lowercaseText.indexOf(lowerCaseSearch);
    if (firstMatch !== -1) {
        if (limitLength && text.length > maxTextSize) {
            const start = (firstMatch - maxTextSize / 2) > 0 ? (firstMatch - maxTextSize / 2) : 0;
            const end = firstMatch + maxTextSize / 2;
            text = text.substring(start, end);
            lowercaseText = lowercaseText.substring(start, end);
        }
    } else {
        if (limitLength && text.length > maxTextSize) {
            text = text.substring(0, maxTextSize) + "...";
        }
    }

    while (index !== -1) {
        index = lowercaseText.indexOf(lowerCaseSearch);
        if (index !== -1) {

            text = text.substr(0, index + offset) + startReplacement + text.substr(index + offset);
            text = text.substr(0, index + startReplacement.length + search.length + offset) + endReplacement + text.substr(index + startReplacement.length + search.length + offset);
            offset += (index + offsetCount + search.length);
        }
        lowercaseText = lowercaseText.substring(index + search.length);
    }

    if (limitLength && (firstMatch + maxTextSize / 2) < textLength) {
        text = text + "...";
    }
    if (limitLength && (firstMatch - maxTextSize / 2) > 0) {
        text = "..." + text;
    }
    return text;
}
export const highlightDescription = (text: string, search: string) => {
    if (!text?.length || !search.length) {
        return [];
    }

    const searchLowercase = search.toLowerCase();
    let lines = text.split("\n");
    lines = lines.filter(line => {
        return line.toLocaleLowerCase().indexOf(searchLowercase) !== -1;
    }).map(line => line.trim()).map(line => {
        const timecodeRegex = /^[0-9.:]+ - (.*)/;
        if (timecodeRegex.test(line)) {
            return timecodeRegex.exec(line)[1];
        }
        return line;
    });
    return lines;
}

export const getStartTimeFromTimecodeLine = (text: string): string => {
    const timecodeRegex = /([0-9]{1,2}):([0-9]{1,2})(?::([0-9]{1,2}))?/;
    if (timecodeRegex.test(text)) {
        const timecode = timecodeRegex.exec(text).filter(n => !!n).map(n => parseInt(n));
        return `${timecode.length === 4 ? timecode[1] * 3600 + timecode[2] * 60 + timecode[3] : timecode[1] * 60 + timecode[2]}`;
    }
    return '';
}
