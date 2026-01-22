const serialize = (obj: any, prefix = null) => {
    return Object.keys(obj).map(key => {
        let value = obj[key];
        const newKey = prefix ? `${prefix}[${key}]` : key;

        if (value === true) {
            value = 1;
        }
        if (value === false) {
            value = 0;
        }
        if (value === null || value === undefined || value?.length === 0 || value === -1) {
            return '';
        }

        if (value && typeof value === 'object') {
            return serialize(value, newKey);
        }
        return `${encodeURIComponent(newKey)}=${encodeURIComponent(value)}`;
    }).filter(i => i?.length).join('&');
}

export const updateQueryString = (data: any) => {
    window.history.replaceState({}, '', `${location.pathname}?${serialize(data)}`);
}
