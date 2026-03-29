import { Database } from "./database";
import { createPlayer } from "./players";
import { getRandomDurationPoint } from "../utils";

export const Playback = {
    playerRoot: document.getElementById('player'),
    noise: document.getElementById('noise'),
    overlay: document.getElementById('overlay'),

    currentChannelNumber: document.getElementById('channel_number'),
    currentChannelName: document.getElementById('channel_name'),
    currentRecordTitle: document.getElementById('record_title'),

    record: null as Promo.Record,
    params: {} as Promo.PlaybackParams,
    player: null as Promo.Player,

    hideTitleOverlayTimeout: null,

    setParamsAndStart(params: Promo.PlaybackParams = {}) {
        const force = this.params.channel_id && this.params.channel_id === params.channel_id;

        this.params = params;
        this.start(force);
    },

    async start(force: boolean = false): Promise<void> {
        this.overlay.style.display = 'none';
        this.noise.style.display = 'block';

        // todo: handle not found, skip n last played
        const [record, seekTo] = Database.records.get(this.params, force);
        this.record = record;

        console.log(this.record, seekTo);

        this.player?.stop();

        this.player = createPlayer(this.record[2]);

        this.player.on('error', () => {
            console.log('error 1');
            this.start();
        });
        this.player.on('ended', () => {
            console.log('ended');
            this.start();
        });
        this.player.on('started', () => {
            this.showTitle();
            this.noise.style.display = 'none';

            const endsAt = new Date().getTime() + (this.player.getDuration() - this.player.getCurrentTime()) * 1000;
            Database.records.updateCurrentPlaying(this.record, endsAt);
        });

        await this.player.load(this.record[2], this.playerRoot);
        const seekToTime = seekTo ?? getRandomDurationPoint(this.player.getDuration());
        this.player.seek(seekToTime);
        this.player.play();
    },

    showTitle() {
        clearTimeout(this.hideTitleOverlayTimeout);

        this.currentRecordTitle.innerHTML = this.record[1];

        const [channelNumber, channelName] = Database.channels.getParamsForRecord(this.record);
        this.currentChannelNumber.innerHTML = channelNumber;
        this.currentChannelName.innerHTML = channelName;

        this.overlay.style.display = '';
        this.hideTitleOverlayTimeout = setTimeout(() => {
            this.overlay.style.display = 'none';
        }, 5000)
    }
}
