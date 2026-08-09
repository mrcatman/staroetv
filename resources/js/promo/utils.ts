export const getRandomDurationPoint = (duration: number) => {
    const maxPointPercent = .65;
    return Math.random() * maxPointPercent * duration;
}

export class EventEmitter {
    on(name: string, callback: Function) {
        if (this[name]) {
            this[name].push(callback);
        } else {
            this[name] = [callback];
        }
    }

    emit(name: string, ...args: any[]) {
        if (this[name]) {
            this[name].forEach(callback => callback(...args));
        }
    }
}

export const getRandomItem = <T>(items: T[]): T => {
    return items[Math.floor(Math.random() * items.length)];
}

export const getRandomItems = <T>(items: T[], count: number): T[] => {
    if (count > items.length) {
        count = items.length;
    }

    const result = new Set<T>();
    while(result.size < count && result.size < items.length) {
        result.add(items[Math.floor(Math.random() * items.length)]);
    }
    return [...result];
}

export const getFullPictureUrl = (url: string) => {
    return url && url.startsWith('/') ? `https://staroetv.su${url}` : url;
}

export const isSafari = () => {
    return !CSS.supports('height', '100dvh') || !('showOpenFilePicker' in window);
}
