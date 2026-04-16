import { Loader } from "./loader";
import { Controls } from "./controls";
import { getFullPictureUrl, getRandomItem, getRandomItems } from "../utils";
import { Resources } from "./resources";

const RECORDS_PAGES_COUNT = 10;

const cacheParam = `?date=${new Date().toLocaleDateString()}`;

export const Database = {
    genres: [] as Promo.Genre[],
    channels: {
        list: [] as Promo.Channel[],
        getFederal(): Promo.Channel[] {
            return this.list.filter(channel => {
                return !!channel[3];
            });
        },
        getParamsForRecord(record: Promo.Record): [number, number, string] {
            const channel = this.list.find(channel => channel[0] == record[5]);
            if (!channel) {
                return [-1, -1, ''];
            }
            if (record[8]) {
                return [0, 0, 'Рекламные ролики'];
            }

            if (!channel[6].length) {
                return [channel[0], channel[5], channel[1]];
            }

            const recordDate = new Date(record[3]);
            if (!recordDate) {
                return [channel[0], channel[5], channel[1]];
            }

            const name = channel[6].filter(name => !!name[1]).reverse().find(name => {
                const nameDate = new Date(name[1]);
                return nameDate <= recordDate;
            })
            return [channel[0], channel[5], name?.[0] || channel[1]];
        },
        getIndexForRecord(record: Promo.Record): number {
            if (record[8]) {
                return 0;
            }
            return this.list.findIndex(channel => channel[0] == record[5]) + 1;
        },
        getNameAndLogo(channel: Promo.Channel, year?: number): [string, string] {
            let name = channel[1];
            let logo = channel[2];
            if (year && channel[6]?.length) {
                let yearName = channel[6].find((channelName) => {
                    return new Date(channelName[2]) >= new Date(year, 1, 1);
                });
                if (!yearName) {
                    yearName = channel[6].find((channelName) => {
                        return !channelName[2];
                    });
                }

                if (yearName) {
                    if (yearName[0].length) {
                        name = yearName[0];
                    }
                    if (yearName[3]?.length) {
                        logo = yearName[3];
                    }
                }
            }
            return [name, logo];
        }
    },
    programs: {
        list: [] as Promo.Program[],
        availableRecordsCounts: {} as Record<number, number>,
        getRandomList(count: number = 10, params: Promo.PlaybackParams): Promo.Program[] {
            let list = this.list.filter(program => {
                return this.availableRecordsCounts[program[0]] > 0;
            });
            if (!params.channel_id) {
                list = list.filter(program => {
                    return !!program[6];
                });
            }

            if (params.genre_id) {
                list = list.filter(program => {
                    return program[3] === params.genre_id;
                });
            }
            if (params.channel_id) {
                list = list.filter(program => {
                    return program[5] === params.channel_id;
                });
            }
            if (params.year) {
                list = list.filter(program => {
                    return program[4]?.includes(params.year);
                });
            }
            return getRandomItems(list, count);
        }
    },
    records: {
        list: [] as Promo.Record[],
        loadedPages: [],
        lastPlayed: {},
        nowPlaying: {} as Promo.NowPlayingRecords,

        async loadPage(page: number) {
            if (this.loadedPages.includes(page)) {
                return;
            }

            const response = await fetch(`/promo/records-${page}.json${cacheParam}`);
            let records = await response.json();

            this.list = [...this.list, ...records];
            this.loadedPages.push(page);
            Database.filterMedia();
        },

        loadAll() {
            for (let i = 1; i <= RECORDS_PAGES_COUNT; i++) {
                this.loadPage(i);
            }
        },

        async get(params: Promo.PlaybackParams, force: boolean = false): Promise<[Promo.Record, number?]> {
            return new Promise((resolve) => {
                const now = new Date().getTime();
                if (params.channel_id && !params.program_id && !force && this.nowPlaying[params.channel_id] && this.nowPlaying[params.channel_id].ends_at > now) {
                    const seekTo = (this.nowPlaying[params.channel_id].ends_at - now) / 1000;
                    return resolve([this.nowPlaying[params.channel_id].record, seekTo]);
                }

                let item = this.find(params);
                let initialItem = null;
                if (item && this.lastPlayed[item[5]] && this.lastPlayed[item[5]].includes(item[0])) {
                    initialItem = item;
                    item = null;
                }

                const addToLastPlayed = (record: Promo.Record) => {
                    //console.log('last played', this.lastPlayed);
                    if (!record) {
                        return;
                    }
                    if (!this.lastPlayed[record[5]]) {
                        this.lastPlayed[record[5]] = [];
                    }
                    this.lastPlayed[record[5]].push(record[0]);
                    this.lastPlayed[record[5]] = this.lastPlayed[record[5]].slice(-5);
                }

                if (!item && this.loadedPages.length < RECORDS_PAGES_COUNT) {
                    for (let i = 1; i <= RECORDS_PAGES_COUNT; i++) {
                        this.loadPage(i).then(() => {
                            item = this.find(params);
                            if (item) {
                                addToLastPlayed(item);
                                resolve([item]);
                            }
                            if (this.loadedPages.length === RECORDS_PAGES_COUNT) {
                                addToLastPlayed(initialItem);
                                resolve([initialItem]);
                            }
                        });
                    }
                } else {
                    item = item || initialItem;

                    addToLastPlayed(item);
                    resolve([item]);
                }
            });
        },
        find(params: Promo.PlaybackParams): Promo.Record {
            let list = this.list;
            if (params.commercials) {
                list = list.filter(record => {
                    return record[8] === true;
                });
            }
            if (params.year) {
                list = list.filter(record => {
                    return record[4] == params.year;
                });
            }
            if (params.channel_id) {
                list = list.filter(record => {
                    return record[5] == params.channel_id && !record[8];
                });
            }
            if (params.program_id) {
                list = list.filter(record => {
                    return record[6] == params.program_id;
                });
            }
            if (params.genre_id) {
                list = list.filter(record => {
                    return record[9] == params.genre_id;
                });
            }
            return getRandomItem(list);
        },
        updateNowPlaying(record: Promo.Record, ends_at: number) {
            this.nowPlaying[record[5]] = {
                record,
                ends_at
            }
        },
        clearNowPlaying() {
            this.nowPlaying = {};
        },
        filterAvailable(list: Promo.Record[]) {
            const youtubeAvailable = Resources.isYoutubeAvailable();
            return list.filter(record => {
                if (record[2].includes('youtube.com')) {
                    return youtubeAvailable;
                }

                return true;
            });
        }
    },
    filterMedia() {
        this.records.list = this.records.filterAvailable(this.records.list);
        this.programs.availableRecordsCounts = {};
        this.records.list.forEach(record => {
            this.programs.availableRecordsCounts[record[6]] = (this.programs.availableRecordsCounts[record[6]] || 0) + 1;
        });
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
        let mainLoaded = false;
        let recordsLoaded = false;
        const initMediaIfReady = () => {
            if (!mainLoaded || !recordsLoaded) {
                return;
            }
            this.filterMedia();
            Controls.initMedia();
        }

        this.loadMain().then(() => {
            mainLoaded = true;
            initMediaIfReady();
            Loader.increment(15);
        });
        this.records.loadPage(1).then(() => {
            recordsLoaded= true;
            initMediaIfReady();
            Loader.increment(15)
        });
    }
}

window.Database = Database;
