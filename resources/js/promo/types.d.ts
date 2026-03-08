namespace Promo {
    interface Video {
        id: number,
        channel_name: string,
        title: string,
        url: string,
    }

    interface VideoParams {
        channel_id?: number,
    }

    interface Player  {
        on(name: string, callback: Function): void;
        emit(name: string, ...args: any[]): void;

        load(video: Promo.Video, container?: HTMLDivElement): Promise<void>;
        play(): void;
        stop(): void;
        seekToRandomTime(): void;
    }
}
