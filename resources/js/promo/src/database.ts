import { Loader } from "./loader";
import { Controls } from "./controls";
import { getFullPictureUrl, getRandomItem, getRandomItems } from "../utils";

const RECORDS_PAGES_COUNT = 10;

export const Database = {
    genres: [],
    loadedRecordsPages: [],

    channels: {
        list: [] as Promo.Channel[],
        getFederal(): Promo.Channel[] {
            return this.list.filter(channel => {
                return !!channel[3];
            });
        },
        getParamsForRecord(record: Promo.Record): [number, string] {
            const channel = this.list.find(channel => channel[0] == record[4]);
            if (!channel) {
                return [0, ''];
            }
            if (!channel[6].length) {
                return [channel[5], channel[1]];
            }

            const recordDate = new Date(record[3]);
            console.log(recordDate);
            if (!recordDate) {
                return [channel[5], channel[1]];
            }

            const name = channel[6].filter(name => !!name[1]).reverse().find(name => {
                const nameDate = new Date(name[1]);
                return nameDate <= recordDate;
            })
            return [channel[5], name?.[0] || channel[1]];
        }
    },
    programs: {
        list: [] as Promo.Program[],
        getRandomList(count: number = 10): Promo.Program[] {
            return getRandomItems(this.list, count);
        }
    },
    records: {
        list: [],
        currentPlaying: {} as Promo.CurrentPlayingRecords,
        get(params: Promo.PlaybackParams, force: boolean = false): [Promo.Record, number?] {
            let list = this.list;

            if (params.channel_id) {
                const now = new Date().getTime();
                if (!force && this.currentPlaying[params.channel_id] && this.currentPlaying[params.channel_id].ends_at > now) {
                    const seekTo = (this.currentPlaying[params.channel_id].ends_at - now) / 1000;
                    return [this.currentPlaying[params.channel_id].record, seekTo];
                }

                list = list.filter(record => {
                    return record[4] == params.channel_id;
                });
            }
            if (params.program_id) {
                list = list.filter(record => {
                    return record[5] == params.program_id;
                });
            }
            return [getRandomItem(list)];
        },
        updateCurrentPlaying(record: Promo.Record, ends_at: number) {
            this.currentPlaying[record[4]] = {
                record,
                ends_at
            }
        }
    },
    async loadMain() {
        const response = await fetch('/promo/index.json');
        const database = await response.json();

        this.channels.list = database.channels.map((channel: Promo.Channel) => {
            channel[2] = getFullPictureUrl(channel[2]);
            return channel;
        });

        this.programs.list = database.programs.map((program: Promo.Program) => {
            program[2] = getFullPictureUrl(program[2]);
            return program;
        });

        this.genres = database.genres;
    },

    async loadRecordsPage(page: number) {
        if (this.loadedRecordsPages.includes(page)) {
            return;
        }

        const response = await fetch(`/promo/records-${page}.json`);
        const database = await response.json();

        this.records.list = [...this.records.list, ...database];
        this.loadedRecordsPages.push(page);
    },

    loadAllPages() {
        for (let i = 1; i <= RECORDS_PAGES_COUNT; i++) {
            this.loadRecordsPage(i);
        }
    },

    loadRequired() {
        this.loadMain().then(() => {
            Loader.increment(15);
            Controls.initAll();
        });
        this.loadRecordsPage(1).then(() => {Loader.increment(15)});
    }
}
