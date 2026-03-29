import Hls from 'hls.js';
import { EventEmitter, getRandomDurationPoint } from "../utils";

class VKPlayer extends EventEmitter implements Promo.Player  {
    private instance;
    private isEnded: boolean = false;

    load(url: string, container: HTMLDivElement)  {
        return new Promise<void>((resolve, reject) => {
            container.innerHTML = `<iframe src="${url}&js_api=1" frameborder="0" allowfullscreen></iframe>`;

            const iframe = container.querySelector('iframe');
            this.instance = VK.VideoPlayer(iframe);
            this.instance.on('inited', () => {
                resolve();
            })
            console.log(this.instance);

            this.instance.on('started', () => this.emit('started'));
            this.instance.on('error', () => this.emit('error'));
            this.instance.on('timeupdate', () => {
                if (this.isEnded) {
                    return;
                }
                const percent = this.instance.getCurrentTime() / this.instance.getDuration();
                if (percent > .999) {
                    this.isEnded = true;
                    this.emit('ended');
                }
            });
        });
    }

    seek(time: number) {
        this.instance.seek(time);
    }

    play() {
        this.instance?.play();
    }

    stop() {
        this.instance?.pause();
    }

    getCurrentTime() {
        return this.instance.getCurrentTime();
    }

    getDuration() {
        return this.instance.getDuration();
    }
}

class RutubePlayer extends EventEmitter implements Promo.Player {
    private contentWindow;

    load(url: string, container: HTMLDivElement)  {
        return new Promise<void>((resolve, reject) => {
            container.innerHTML = `<iframe src="${url}" frameborder="0" allowfullscreen allow="clipboard-write; autoplay" ></iframe>`;

            const iframe = container.querySelector('iframe');
            this.contentWindow = iframe.contentWindow;
            this.contentWindow.addEventListener('message',  (event) => {
                console.log(event);
            });
            resolve();
        });
    }

    private doCommand(command) {
        this.contentWindow?.postMessage(JSON.stringify({command}), '*');
    }

    seek() {
        // TODO
    }

    play() {
        this.doCommand( {type:'player:play', data: {}});
    }

    stop() {
        this.doCommand( {type:'player:pause', data: {}});
    }

    getCurrentTime() {
        // TODO
    }

    getDuration() {
        // TODO
    }
}

class YoutubePlayer extends EventEmitter implements Promo.Player {
    private instance;
    load(url: string, container: HTMLDivElement)  {
        return new Promise<void>((resolve, reject) => {
            container.innerHTML = '';
            const player = document.createElement('div');
            container.appendChild(player);

            this.instance = new YT.Player(player, {
                videoId: url.split('/').pop(),
                playerVars: {
                    origin: 'https://www.youtube.com',
                    enablejsapi: 1,
                    rel: 0,
                    disablekb: 1,
                    controls: 0,
                    autoplay: 0
                },
                events: {
                    onReady: () => {
                        resolve();
                    },
                    onStateChange: ({data}) => {
                        switch (data) {
                            case 0:
                                this.emit('ended');
                                break;
                            case 1:
                                this.emit('started');
                                break;
                            default:
                                break;
                        }
                    },
                    onError: (e) => {
                        this.emit('error');
                    }
                }
            });
        });
    }

    seek(time: number) {
        this.instance.seekTo(time);
    }

    play() {
        this.instance.playVideo();
    }

    stop() {
        this.instance.stopVideo();
    }

    getCurrentTime() {
        return this.instance.getCurrentTime();
    }

    getDuration() {
        return this.instance.getDuration();
    }
}

class HlsJsPlayer extends EventEmitter implements Promo.Player {
    private videoElement: HTMLVideoElement;

    load(url: string, container: HTMLDivElement)  {
        return new Promise<void>((resolve, reject) => {
            container.innerHTML = `<video></video>`;
            this.videoElement = container.querySelector('video');

            if (Hls.isSupported()) {
                const hls = new Hls();
                hls.loadSource(url);
                hls.attachMedia(this.videoElement);
                this.videoElement.addEventListener('playing', () => this.emit('started'));
                hls.on(Hls.Events.MANIFEST_PARSED, () =>  resolve());

                hls.on(Hls.Events.MEDIA_ENDED, () => this.emit('ended'));
                hls.on(Hls.Events.ERROR, () => this.emit('error'));
            } else if (this.videoElement.canPlayType('application/vnd.apple.mpegurl')) {
                this.videoElement.src = url;
                this.videoElement.addEventListener('loadedmetadata', () => resolve());

                this.videoElement.addEventListener('playing', () => this.emit('started'));
                this.videoElement.addEventListener('ended', () => this.emit('ended'));
                this.videoElement.addEventListener('error', () => this.emit('error'));
            } else {
                reject();
            }
        });
    }

    seek(time: number) {
        this.videoElement.currentTime = time;
    }

    play() {
        this.videoElement?.play();
    }

    stop() {
        this.videoElement?.pause();
    }

    getCurrentTime() {
        return this.videoElement.currentTime;
    }

    getDuration() {
        return this.videoElement.duration;
    }
}

class HTMLPlayer extends EventEmitter implements Promo.Player {
    private videoElement: HTMLVideoElement;

    load(url: string, container: HTMLDivElement)  {
        return new Promise<void>((resolve, reject) => {
            container.innerHTML = `<video><source src="${url}" type="video/mp4" /></video>`;
            this.videoElement = container.querySelector('video');
            this.videoElement.addEventListener('loadedmetadata', function() {
                resolve();
            });
            this.videoElement.addEventListener('playing', () => this.emit('started'));
            this.videoElement.addEventListener('ended', () => this.emit('ended'));
            this.videoElement.addEventListener('error', () => this.emit('error'));
        });
    }

    seek(time: number) {
        this.videoElement.currentTime = time;
    }

    play() {
        this.videoElement?.play();
    }

    stop() {
        this.videoElement?.pause();
    }

    getCurrentTime() {
        return this.videoElement.currentTime;
    }

    getDuration() {
        return this.videoElement.duration;
    }
}

export const createPlayer = (url: string): Promo.Player | null => {
    let player: Promo.Player;
    switch (true) {
        case url.includes('vk.com'):
            player = new VKPlayer();
            break;
        case url.includes('rutube'):
            player = new RutubePlayer();
            break;
        case url.includes('youtu'):
            player = new YoutubePlayer();
            break;
        case url.includes('.m3u8'):
            player = new HlsJsPlayer();
            break;
        default:
            player = new HTMLPlayer();
            break;
    }
    return player;
}
