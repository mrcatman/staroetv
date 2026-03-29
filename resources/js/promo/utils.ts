export const getRandomDurationPoint = (duration: number) => {
    const maxPointPercent = .8;
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
        console.log('emit', name, args, this[name]);
        if (this[name]) {
            this[name].forEach(callback => callback(...args));
        }
    }
}

export const getRandomItem = <T>(items: T[]): T => {
    return items[Math.floor(Math.random() * items.length)];
}

export const getRandomItems = <T>(items: T[], count: number): T[] => {
    const randomItems: T[] = [];
    const indexes = [];

    let randomIndex = Math.floor(Math.random() * items.length);
    while (indexes.indexOf(randomIndex) === -1 && randomItems.length < count) {
        indexes.push(randomIndex);
        randomItems.push(items[randomIndex]);
        randomIndex = Math.floor(Math.random() * items.length);
    }

    return randomItems
}

export const getFullPictureUrl = (url: string) => {
    return url && url.startsWith('/') ? `https://staroetv.su${url}` : url;
}
