import { Database } from "./database";
import { createPlayer } from "./players";
import { getRandomDurationPoint } from "../utils";
import { Controls } from "./controls";

export const Playback = {
    playerRoot: document.getElementById('player'),
    noise: document.getElementById('noise'),
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

    hideTitleOverlayTimeout: null,
    firstStart: true,
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
        return await this.start(force, skipOnNonExisting);
    },

    async start(force: boolean = false, skipOnNonExisting: boolean = false): Promise<boolean> {
        this.active = true;
        if (this.firstStart) {
            this.firstStart = false;
            Database.records.filterAvailable();
        }

        this.destroy();

        this.notFound.style.display = 'none';
        this.overlay.style.display = 'none';
        this.noise.style.display = 'block';
        this.playerRoot.style.display = 'block';

        // todo: skip n last played
        const [record, seekTo] = await Database.records.get(this.params, force);
        console.log('Start record', record, this.params);

        if (!record) {
            this.notFound.style.display = '';
            this.stop();
            if (!skipOnNonExisting) {
                this.noise.style.display = 'none';
            }
            return false;
        }

        this.record = record;
        this.currentChannelIndex = Database.channels.getIndexForRecord(this.record);

        this.player = createPlayer(this.record[2]);

        this.player.on('error', (e) => {
            console.log('Player error: ', e);

            this.destroy();
            this.start();
        });
        this.player.on('ended', () => {
            console.log('Playback ended');
            this.start();
        });
        this.player.on('started', () => {
            this.playedRecordsCount++;

            this.showTitle();
            this.noise.style.display = 'none';

            const endsAt = new Date().getTime() + (this.player.getDuration() - this.player.getCurrentTime()) * 1000;
            Database.records.updateNowPlaying(this.record, endsAt);
        });

        await this.player.load(this.record[2], this.playerRoot);
        const seekToTime = seekTo ?? getRandomDurationPoint(this.player.getDuration());
        this.player.seek(seekToTime);
        this.player.play();

        if (this.playedRecordsCount >= 2) {
            Database.records.loadAll();
        }
        return true;
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
    },
    setVolume(volume: number) {
        this.player?.setVolume(volume);
    },
    async changeChannel(delta: number) {
        let state = false;
        while (!state) {
            this.currentChannelIndex += delta;
            const channel = Database.channels.getByIndex(this.currentChannelIndex);
            state = await this.setParamsAndStart({
                channel_id: channel[0]
            }, true);
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
    }
}
