import { Database } from "./database";
import { createPlayer } from "./players";
import { getRandomDurationPoint } from "../utils";
import { Controls } from "./controls";

const RECORD_PLAY_TIMEOUT = 1000 * 12;

export const Playback = {
    playerRoot: document.getElementById('player'),
    inner: document.getElementById('inner'),
    noise: document.getElementById('noise'),
    intro: document.getElementById('intro'),
    overlay: document.getElementById('overlay'),
    notFound: document.getElementById('not_found'),

    currentChannelNumber: document.getElementById('channel_number'),
    currentChannelName: document.getElementById('channel_name'),
    currentRecordTitle: document.getElementById('record_title'),
    currentRecordYear: document.getElementById('record_year'),
    currentChannelIndex: 0,

    record: null as Promo.Record,
    params: {} as Promo.PlaybackParams,
    player: null as Promo.Player,

    recordTimeout: null,

    hideTitleOverlayTimeout: null,
    playedRecordsCount: 0,
    updateDisplayStartTimeout: null,
    updateDisplayInterval: null,
    active: false,

    onOff() {
        if (this.active) {
            this.stop();
            this.active = false;
        } else {
            this.start();
        }
    },
    async setParamsAndStart(params: Promo.PlaybackParams = {}, skipOnNonExisting: boolean = false) {
        const force = this.params.channel_id && this.params.channel_id === params.channel_id;

        this.params = {...this.params, ...params};
        return await this.start({force, skipOnNonExisting});
    },

    async start({ force, skipOnNonExisting, doNotSeekToRandomTime, tries} = {force: false, skipOnNonExisting: false, doNotSeekToRandomTime: false, tries: 0}): Promise<boolean> {
        return new Promise(async (resolve) => {
            clearTimeout(this.recordTimeout);

            this.updateDisplay(tries > 0 ? `${tries + 1} попытка поймать канал...` : 'Ловим канал...');

            this.active = true;
            this.destroy();

            this.notFound.style.display = 'none';
            this.overlay.style.display = 'none';
            this.intro.style.opacity = '0';
            this.intro.style.pointerEvents = 'none';

            this.noise.style.opacity = '1';
            this.playerRoot.style.display = 'block';

            const [record, seekTo] = await Database.records.get(this.params, force);

            console.log('Start record', record, this.params);

            if (!record) {
                Controls.setActiveRecord(null);
                this.updateDisplay('Ничего не найдено');
                this.notFound.style.display = '';
                this.stop();
                if (!skipOnNonExisting) {
                    this.intro.style.opacity = '0';
                    this.noise.style.opacity = '0';
                    this.intro.style.pointerEvents = 'none';
                }
                return resolve(false);
            }

            let started = false;

            this.record = record;
            this.currentChannelIndex = Database.channels.getIndexForRecord(this.record);

            this.player = createPlayer(this.record[2]);

            this.player.on('error', (e) => {
                console.log('Player error: ', e);
                Database.records.markError(record);

                this.destroy();
                this.start();

                return resolve(false);
            });
            this.player.on('ended', () => {
                console.log('Playback ended');
                this.start({doNotSeekToRandomTime: this.params.channel_id});
            });
            this.player.on('started', () => {
                started = true;
                this.playedRecordsCount++;
                Controls.setActiveRecord(this.record);
                this.showTitle();
                this.noise.style.opacity = '0';

                this.params.program_id = undefined;

                const endsAt = new Date().getTime() + (this.player.getDuration() - this.player.getCurrentTime()) * 1000;
                Database.records.updateNowPlaying(this.record, endsAt);
                clearTimeout(this.recordTimeout);

                return resolve(true);
            });

            await this.player.load(this.record[2], this.playerRoot);

            if (!this.params.commercials && !doNotSeekToRandomTime) {
                const seekToTime = seekTo ?? getRandomDurationPoint(this.player.getDuration());
                seekToTime && this.player.seek(seekToTime);
            }

            this.player.play();
            setTimeout(() => {
                try {
                    this.player.setVolume(1);
                } catch (e) { }
            }, 200);

            this.recordTimeout = setTimeout(() => {
                console.log('started', started);
                if (!started) {
                    console.log(`Record not started in ${RECORD_PLAY_TIMEOUT}ms, trying a new one...`);
                    this.start({
                        force: true,
                        skipOnNonExisting,
                        doNotSeekToRandomTime,
                        tries: tries + 1
                    })
                }
            }, RECORD_PLAY_TIMEOUT);

            if (this.playedRecordsCount >= 2) {
                Database.records.loadAll();
            }
        });
    },
    destroy() {
        this.playerRoot.style.display = 'none';
        this.playerRoot.innerHTML = '';
        try {
            this.player?.stop();
        } catch (e) {}
    },
    stop() {
        this.destroy();

        clearTimeout(this.updateDisplayStartTimeout);
        clearInterval(this.updateDisplayInterval);
        this.currentRecordTitle.style.display = 'none';

        clearTimeout(this.hideTitleOverlayTimeout);
        this.overlay.style.display = 'none';

        Controls.setActiveRecord(null);
        this.intro.style.opacity = '1';
        this.intro.style.pointerEvents = '';
    },
    setVolume(volume: number) {
        this.player?.setVolume(volume);
    },
    async changeChannel(delta: number) {
        let state = false;
        while (!state) {
            this.currentChannelIndex += delta;
            if (this.currentChannelIndex < 0) {
                this.currentChannelIndex = Database.channels.list.length - 1;
            }
            if (this.currentChannelIndex > Database.channels.list.length) {
                this.currentChannelIndex = 0;
            }
            if (this.currentChannelIndex === 0) {
                state = await this.setParamsAndStart({
                    channel_id: undefined,
                    program_id: undefined,
                    commercials: true
                }, true);
            } else {
                const channel = Database.channels.list[this.currentChannelIndex - 1];
                state = await this.setParamsAndStart({
                    channel_id: channel[0],
                    program_id: undefined,
                    commercials: undefined
                }, true);
            }

        }
    },
    showTitle() {
        clearTimeout(this.hideTitleOverlayTimeout);

        this.updateDisplay(this.record[1]);

        const [channelId, channelNumber, channelName] = Database.channels.getParamsForRecord(this.record);
        this.currentChannelNumber.innerHTML = channelNumber;
        this.currentChannelName.innerHTML = channelName;

        Controls.updateActiveChannel(channelId);

        //this.currentRecordYear.innerHTML = new Date(this.record[3]).getFullYear().toString();

        this.overlay.style.display = '';
        this.hideTitleOverlayTimeout = setTimeout(() => {
            this.overlay.style.display = 'none';
        }, 5000)
    },
    updateDisplay(title: string, immediate: boolean = false) {
        clearTimeout(this.updateDisplayStartTimeout);
        clearInterval(this.updateDisplayInterval);

        const MAX_SYMBOLS = 38;
        if (title.length <= MAX_SYMBOLS) {
            this.currentRecordTitle.innerHTML = title;
            return;
        }

        let start = 0;
        let cycles = 0;
        const doubleTitle = `${title}   |   ${title}`;
        const update = () => {
            this.currentRecordTitle.innerHTML = doubleTitle.substring(start, start + MAX_SYMBOLS);
        }
        this.currentRecordTitle.style.display = 'block';
        update();

        this.updateDisplayStartTimeout = setTimeout(() => {
            clearInterval(this.updateDisplayInterval);
            this.updateDisplayInterval = setInterval(() => {
                if (start > title.length) {
                    cycles++;
                    start = 0;
                } else {
                    start++;
                }
                update();
                if (start === 0 && cycles === 1) {
                    clearInterval(this.updateDisplayInterval);
                }
            }, 200);
        }, immediate ? 0 : 3000);
    },
    init() {
        this.currentRecordTitle.addEventListener('click', () => {
            if (!this.record) {
                return;
            }
            this.updateDisplay(this.record[1], true);
        });
        this.inner.addEventListener('click', () => {
            this.start({ force: true });
        });
        this.intro.currentTime = Math.random() * 35;
    }
}
