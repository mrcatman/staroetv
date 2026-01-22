namespace Common {
    export type Date = {
        year: number,
        month: number,
        day: number,
        range?: boolean
        year_start?: number
        month_start?: number
        day_start?: number
        year_end?: number
        month_end?: number
        day_end?: number
    }

    export type Period = {
        name: string;
        url: string;
        years: [number, number];
    }
}
