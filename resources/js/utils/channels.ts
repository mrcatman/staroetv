export const channelCategories = {
    'federal': 'Федеральные',
    'regional': 'Региональные',
    'abroad': 'Зарубежные',
    'other': 'Другие'
}

export const filterChannel = (channel: Models.Channel, type: string) => {
    if (type === 'federal') {
        return channel.is_federal && !channel.city;
    }
    if (type === 'regional') {
        return channel.is_regional || !!channel.city;
    }
    if (type === 'abroad') {
        return channel.is_abroad;
    }
    if (type === 'other') {
        return !channel.is_federal && !channel.is_regional && !channel.is_abroad;
    }
    return false;
}

export const getDisplayName = (_channel: Models.Channel) => {
    if (_channel.city && _channel.city !== '') {
        return `${_channel.name} (${_channel.city})`;
    } else {
        if (_channel.country && _channel.country !== '') {
            return `${_channel.name}  (${_channel.country})`;
        }
    }
    return _channel.name;
}

export const getAdditionalNames = (channel: Models.Channel) => {
    const additionalNames = [...new Set(channel.names.filter(name => name.name && name.name !== '' && name.name !== channel.name).map(name => name.name))];
    return additionalNames.join(', ');
}

const normalizeName = (name: string) => {
    return name.toLowerCase().replace('-', '');
}

export const findByName = (name: string, list: Models.Channel[]): {
    channel: Models.Channel | null,
    name: string | null
} => {
    const normalizedName = normalizeName(name);

    for (const channel of list) {
        if (normalizeName(channel.name) === normalizedName) {
            return {
                channel,
                name: channel.name
            };
        }
        for (const name of channel.names) {
            if (normalizeName(name.name) === normalizedName) {
                return {
                    channel,
                    name: name.name
                };
            }
            if (name.alternatives?.length) {
                for (const alternative of name.alternatives) {
                    if (normalizeName(alternative) === normalizedName) {
                        return {
                            channel,
                            name: name.name
                        };
                    }
                }
            }
        }
    }

    for (const channel of list) {
        if (normalizeName(channel.name).includes(normalizedName)) {
            return {
                channel,
                name: channel.name
            };
        }
        for (const name of channel.names) {
            if (normalizeName(name.name).includes(normalizedName)) {
                return {
                    channel,
                    name: name.name
                };
            }
            if (name.alternatives?.length) {
                for (const alternative of name.alternatives) {
                    if (normalizeName(alternative).includes(normalizedName)) {
                        return {
                            channel,
                            name: name.name
                        };
                    }
                }
            }
        }
    }
    return {
        channel: null,
        name: null
    };
}

export const getNameByDate = (channel: Models.Channel, date: Common.Date): string | null => {
    if (!channel?.names.length) {
        return null;
    }
    if (date.year <= 0 && date.year_start <= 0) {
        return null;
    }
    const recordDate = new Date(date.year > 0 ? date.year : date.year_start, date.month >= 0 ? date.month - 1 : 0, date.day >= 0 ? date.day - 1 : 0);
    const name = channel.names.filter(name => !!name.date_start).reverse().find(name => {
        if (!name.name) {
            return false;
        }
        const nameDate = new Date(name.date_start);
        return nameDate <= recordDate;
    })
    if (!name) {
        return null;
    }
    return name.name;
}
