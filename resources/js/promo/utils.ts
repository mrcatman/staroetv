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
    return navigator.vendor.match(/apple/i) &&
        !navigator.userAgent.match(/crios/i) &&
        !navigator.userAgent.match(/fxios/i) &&
        !navigator.userAgent.match(/Opera|OPT\//);
}

export const requestFullscreen = (el: HTMLElement) => {
    if (el.requestFullscreen) {
        el.requestFullscreen();
    } else if (elwebkitRequestFullscreen) { /* Safari */
        el.webkitRequestFullscreen();
    } else if (el.msRequestFullscreen) { /* IE11 */
        el.msRequestFullscreen();
    }
}

export const exitFullscreen = () => {
    if (document.exitFullscreen) {
        document.exitFullscreen();
    } else if (document.webkitExitFullscreen) {
        document.webkitExitFullscreen();
    } else if (document.mozCancelFullScreen) {
        document.mozCancelFullScreen();
    } else if (document.msExitFullscreen) {
        document.msExitFullscreen();
    }
}
