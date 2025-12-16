export const channelCategories = {
    'federal': 'Федеральные',
    'regional': 'Региональные',
    'abroad': 'Зарубежные',
    'other': 'Другие'
}

export const filterChannel = (channel: Models.Channel, type: string) => {
    if (type === 'federal') {
        return channel.is_federal;
    }
    if (type === 'regional') {
        return channel.is_regional;
    }
    if (type === 'abroad') {
        return channel.is_abroad;
    }
    if (type === 'other') {
        return !channel.is_federal && !channel.is_regional && !channel.is_abroad;
    }
    return false;
}
