export const declination = (number: number, titles: string[]) => {
    const cases = [2, 0, 1, 1, 1, 2];
    return titles[ (number% 100 > 4 && number % 100 < 20) ? 2 : cases[Math.min(number % 10, 5)] ].replace('[number]', number.toString());
}
