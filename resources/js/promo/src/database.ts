import { Loader } from "./loader";
import { Controls } from "./controls";
import { getFullPictureUrl, getRandomItem, getRandomItems } from "../utils";
import { Resources } from "./resources";

const RECORDS_PAGES_COUNT = 10;

const cacheParam = `?date=${new Date().toLocaleDateString()}`;

export const Database = {
    genres: [],
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
        },
        getByIndex(index: number): Promo.Channel {
            if (index < 0) {
                return this.list[this.list.length - 1];
            }
            if (index >= this.list.length) {
                return this.list[0];
            }
            return this.list[index];
        },
        getIndexById(id: number): number {
            console.log(id);
            return this.list.findIndex(channel => channel[0] == id);
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
        loadedPages: [],
        currentPlaying: {} as Promo.CurrentPlayingRecords,

        async loadPage(page: number) {
            if (this.loadedPages.includes(page)) {
                return;
            }

            const response = await fetch(`/promo/records-${page}.json${cacheParam}`);
            const database = await response.json();

            this.list = [...this.list, ...database];
            this.loadedPages.push(page);
        },

        loadAll() {
            for (let i = 1; i <= RECORDS_PAGES_COUNT; i++) {
                this.loadPage(i);
            }
        },

        async get(params: Promo.PlaybackParams, force: boolean = false): Promise<[Promo.Record, number?]> {
            return new Promise((resolve) => {
                const now = new Date().getTime();
                if (params.channel_id && !force && this.currentPlaying[params.channel_id] && this.currentPlaying[params.channel_id].ends_at > now) {
                    const seekTo = (this.currentPlaying[params.channel_id].ends_at - now) / 1000;
                    return resolve([this.currentPlaying[params.channel_id].record, seekTo]);
                }

                let item = this.find(params);
                if (!item && this.loadedPages.length < RECORDS_PAGES_COUNT) {
                    for (let i = 1; i <= RECORDS_PAGES_COUNT; i++) {
                        this.loadPage(i).then(() => {
                            item = this.find(params);
                            if (item) {
                                resolve([item]);
                            }
                        });
                    }
                } else {
                    resolve([item]);
                }
            });
        },
        find(params: Promo.PlaybackParams): Promo.Record {
            let list = this.list;
            if (params.channel_id) {
                list = list.filter(record => {
                    return record[4] == params.channel_id;
                });
            }
            if (params.program_id) {
                list = list.filter(record => {
                    return record[5] == params.program_id;
                });
            }
            return getRandomItem(list);
        },
        updateCurrentPlaying(record: Promo.Record, ends_at: number) {
            this.currentPlaying[record[4]] = {
                record,
                ends_at
            }
        },
        filterAvailable() {
            const youtubeAvailable = Resources.isYoutubeAvailable();
            const telegramAvailable = false; // todo

            this.list = this.list.filter(record => {
                if (record[2].includes('youtube.com')) {
                    return youtubeAvailable;
                }
                if (record[2].includes('tgvideo')) {
                    return telegramAvailable;
                }
                return true;
            });
        }
    },
    async loadMain() {
        const response = await fetch(`/promo/index.json${cacheParam}`);
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


    loadRequired() {
        this.loadMain().then(() => {
            Loader.increment(15);
            Controls.initAll();
        });
        this.records.loadPage(1).then(() => {
            Loader.increment(15)
        });
    }
}
