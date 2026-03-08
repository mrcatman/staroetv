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
        if (this[name]) {
            this[name].forEach(callback => callback(...args));
        }
    }
}
