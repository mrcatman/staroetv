declare namespace Promo {

    type Channel = [
        id: number,
        name: string,
        logo: string,
        is_federal: boolean,
        city: string,
        order: number,
        names: Promo.ChannelName[],
        years: number[],
    ]

    type ChannelName = [
        name: string,
        date_start?: string,
        date_end?: string,
        logo?: string,
    ]

    type Program = [
        id: number,
        name: string,
        logo: string,
        genre_id: number,
        years: number[],
        channel_id: number,
    ]

    type Record = [
        id: number,
        title: string,
        playback_url: string,
        date: string,
        year: number,
        channel_id: number,
        program_id: number,
        is_interprogram: boolean,
        is_advertising: boolean,
        genre_id: number,
    ]

    type Genre = [
        id: number,
        name: string,
    ]

    type NowPlayingRecords = {
        [key: number]: {
            record: Promo.Record,
            ends_at: number
        }
    }

    interface PlaybackParams {
        channel_id?: number,
        program_id?: number,
        genre_id?: number,
        year?: number,
        commercials?: boolean,
    }

    interface Player  {
        on(name: string, callback: Function): void;
        emit(name: string, ...args: any[]): void;

        load(url: string, container?: HTMLDivElement): Promise<void>;
        play(): void;
        stop(): void;
        seek(time: number): void;
        getCurrentTime(): number;
        getDuration(): number;
        setVolume(volume: number): void;
    }
}
